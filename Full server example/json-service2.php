<?php
require '../config-production.php';
require 'google-apps-script-card-service.php';
$database = $application->getDatabase();

// Call like dieError('ERROR: you did not login');
function dieError($errorText) {
  $card = CardServiceCardBuilder::newCardBuilder()
    ->setHeader(
      CardServiceCardHeader::newCardHeader()
        ->setTitle($errorText)
    );
  die($card.build());
}

// DEBUGGING ///////////////////////////////////////////////////////////////////
//file_put_contents('TEST-input.json', json_encode($_POST));

////////////////////////////////////////////////////////////////////////////////
// HANDLE ACCESS CONTROL
////////////////////////////////////////////////////////////////////////////////
$signature = $_POST['signature'] or dieError('ERROR: Signature missing');
$detailsString = $_POST['gmail_details'] or dieError('ERROR: Gmail details missing');
$hmacKey = 'PUT A KEY HERE...';
$correctSignature = hash_hmac('sha256', $detailsString, $hmacKey);
if ($signature !== $correctSignature) dieError('ERROR: Signature is incorrect!');

////////////////////////////////////////////////////////////////////////////////
// DECODE INCOMING PAYLOAD
////////////////////////////////////////////////////////////////////////////////
$detailsString = base64_decode($_POST['gmail_details']); // https://stackoverflow.com/q/51866469/300224
$details = json_decode($detailsString) or dieError('ERROR: JSON input error!');
$messageFrom = $details->messageFrom; // string
$messageTo = $details->messageTo; // string
$messageCc = $details->messageCc; // string
$messageBcc = $details->messageBcc; // string
$messagePlainBody = $details->messagePlainBody; // string
$messageSubject = $details->messageSubject; // string
$threadLabels = $details->threadLabels; // an array of strings

////////////////////////////////////////////////////////////////////////////////
// START OUTPUT
////////////////////////////////////////////////////////////////////////////////
$card = CardServiceCardBuilder::newCardBuilder();

////////////////////////////////////////////////////////////////////////////////
// FIND STUDENTS, LINK TO ENROLLMENT WORKROOM
////////////////////////////////////////////////////////////////////////////////
$potentialCustomerEmails = [];
foreach (explode(',', implode(',',[$messageFrom, $messageTo, $messageCc, $messageBcc])) as $email) {
  $email = trim($email);
  if (preg_match('|\<([^\@]+\@[^\>]+)\>|', $email, $matches)) {
    $email = $matches[1];
  }
  if (!str_contains($email, '@') === false) {
    continue;
  }
  if (str_contains($email, '@YourCompanysDomainName') !== false) {
    continue;
  }
  $potentialCustomerEmails[] = $email;
}

$didAddStudentSection = false;
foreach ($potentialCustomerEmails as $email) {
  // Is email address in this database?
  $sql = 'SELECT ID FROM wp_users WHERE user_email = ?';
  $query = $database->prepare($sql);
  $query->execute([$email]);
  $userId = $query->fetchColumn();
  if (empty($userId)) continue;

  // Get user metadata
  $userMetadata = [];
  $sql = 'SELECT * FROM wp_usermeta WHERE user_id = ?';
  $query = $database->prepare($sql);
  $query->execute([$userId]);
  while ($row = $query->fetch()) {
    $userMetadata[$row['meta_key']] = $row['meta_value'];
  }

  $section = CardServiceCardSection::newCardSection();

  $section->addWidget(CardServiceKeyValue::newKeyValue()
    ->setIconUrl('https://example.com/gmail-addon/student.png')
    ->setContent($email)
  );

  $section->addWidget(CardServiceKeyValue::newKeyValue()
    ->setTopLabel('Last online')
    ->setContent(date('Y-m-d', strtotime($userMetadata['last_login_time'])))
  );

  $section->addWidget(CardServiceKeyValue::newKeyValue()
    ->setTopLabel('Student name')
    ->setContent(
      htmlspecialchars(
        ($userMetadata['shipping_first_name'] ?? $userMetadata['first_name']) . ' ' .
        ($userMetadata['shipping_last_name'] ?? $userMetadata['last_name']) . ' ' .
        ($userMetadata['credentials'] ?? '')
      )
    )
  );

  $section->addWidget(CardServiceKeyValue::newKeyValue()
    ->setTopLabel('Address')
    ->setContent(
      htmlspecialchars(
        $userMetadata['shipping_street_address_1'] . ' ' . $userMetadata['shipping_street_address_2'] . ' ' .
        $userMetadata['shipping_city'] . ' ' . $userMetadata['shipping_state_province'] . ' ' . ($userMetadata['shipping_international_province'] ?? '') . ' ' .
        $userMetadata['shipping_postal_code'] . ' ' . $userMetadata['shipping_country']
      )
    )
  );

  $section->addWidget(CardServiceKeyValue::newKeyValue()
    ->setTopLabel('Phone')
    ->setContent(
      htmlspecialchars(
        $userMetadata['shipping_phone_number'] ?? ''
      )
    )
  );

  $sql = 'SELECT * FROM orders WHERE user_id = ? ORDER BY order_id DESC LIMIT 4';
  $query2 = $database->prepare($sql);
  $query2->execute([$userId]);
  while ($order = $query2->fetch()) {
    $section->addWidget(CardServiceKeyValue::newKeyValue()
      ->setTopLabel('Order ' . $order['order_id'])
      ->setContent(
        htmlspecialchars(
          $order['order_date'] . ' / $' . $order['order_price']
        )
      )
    );
  }

  // Show link to workroom
  $section->addWidget(CardServiceButtonSet::newButtonSet()
    ->addButton(CardServiceTextButton::newTextButton()
      ->setText('Enrollment Workroom')
      ->setOpenLink(CardServiceOpenLink::newOpenLink()
        ->setUrl('https://example.com/app/customer.php?email=' . $email)
        ->setOpenAs(new CardServiceOpenAs('FULL_SIZE'))
      )
    )
  );

  $card->addSection($section);
  $didAddStudentSection = true;
}

if (!$didAddStudentSection) {
  $card->addSection(CardServiceCardSection::newCardSection()
    ->addWidget(CardServiceKeyValue::newKeyValue()
    ->setIconUrl('https://example.com/gmail-addon/no-student.png')
    ->setContent('No student in Enrollment Workroom')
    )
  );
}

////////////////////////////////////////////////////////////////////////////////
// LABEL HINTS
////////////////////////////////////////////////////////////////////////////////
$labelHints = json_decode(file_get_contents('label-hints.json'));
foreach ($threadLabels as $label) {
  $section = CardServiceCardSection::newCardSection();
  $section->addWidget(CardServiceKeyValue::newKeyValue()
    ->setIconUrl('https://example.com/gmail-addon/label.png')
    ->setContent($label)
  );
  if (isset($labelHints->{$label})) {
    $section->addWidget(CardServiceTextParagraph::newTextParagraph()
      ->setText($labelHints->{$label})
    );
  } else {
    $section->addWidget(CardServiceTextParagraph::newTextParagraph()
      ->setText('Ask Will to add notes here')
    );
  }
  $card->addSection($section);
}

echo $card->build();

// DEBUGGING ///////////////////////////////////////////////////////////////////
//file_put_contents('TEST-output.json', $card->build());
//file_put_contents('TEST-output2.txt', print_r($card, true));
