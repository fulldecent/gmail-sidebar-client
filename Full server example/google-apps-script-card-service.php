<?php
////////////////////////////////////////////////////////////////////////////////
// Google
////////////////////////////////////////////////////////////////////////////////

abstract class CardServiceBuildable
{
  // Returns Javascript to build this object in Google Apps Script
  abstract function build(): string;
}

class CardServiceCardBuilder extends CardServiceBuildable
{
  private $header = null; // ?CardServiceCardHeader
  private $sections = []; // [CardServiceCardSection]

  private function __construct(){}

  static function newCardBuilder(): CardServiceCardBuilder
  {
    return new self;
  }

  function setHeader(?CardServiceCardHeader $header): CardServiceCardBuilder
  {
    $this->header = $header;
    return $this;
  }

  function addSection(CardServiceCardSection $section): CardServiceCardBuilder
  {
    $this->sections[] = $section;
    return $this;
  }

  function build(): string
  {
    $retval = 'CardService.newCardBuilder()';
    if (isset($this->header)) {
      $retval .= '.setHeader(' . $this->header->build() . ')';
    }
    foreach ($this->sections as $section) {
      $retval .= '.addSection(' . $section->build() . ')';
    }
    $retval .= '.build()';
    return $retval;
  }
}

class CardServiceCardHeader extends CardServiceBuildable
{
  private $title = null; // ?string
  private $subtitle = null; // ?string
  private $imageUrl = null; // ?string
  private $imageStyle = null; // ?CardServiceImageStyle
  private $imageAltText = null; // ?string

  private function __construct(){}

  static function newCardHeader(): CardServiceCardHeader
  {
    return new self;
  }

  function setTitle(?string $title): CardServiceCardHeader
  {
    $this->title = $title;
    return $this;
  }

  function setSubtitle(?string $subtitle): CardServiceCardHeader
  {
    $this->subtitle = $subtitle;
    return $this;
  }

  function setImageUrl(?string $imageUrl): CardServiceCardHeader
  {
    $this->imageUrl = $imageUrl;
    return $this;
  }

  function setImageStyle(/*enum CardServiceImageStyle*/ ?string $imageStyle): CardServiceCardHeader
  {
    $this->imageStyle = $imageStyle;
    return $this;
  }

  function setImageAltText(?string $imageAltText): CardServiceCardHeader
  {
    $this->imageAltText = $imageAltText;
    return $this;
  }

  function build(): string
  {
    $retval = 'CardService.newCardHeader()';
    if (isset($this->title)) {
      $retval .= '.setTitle(' . json_encode($this->title) . ')';
    }
    if (isset($this->subtitle)) {
      $retval .= '.setSubtitle(' . json_encode($this->subtitle) . ')';
    }
    if (isset($this->imageUrl)) {
      $retval .= '.setImageUrl(' . json_encode($this->imageUrl) . ')';
    }
    if (isset($this->imageStyle)) {
      $retval .= '.setImageStyle(' . $this->imageStyle->build() . ')';
    }
    if (isset($this->imageAltText)) {
      $retval .= '.setImageAltText(' . json_encode($this->imageAltText) . ')';
    }
    return $retval;
  }
}

class CardServiceImageStyle extends CardServiceBuildable {
  private $value;
  const values = [
    'SQUARE' => 'CardService.ImageStyle.SQUARE',
    'CIRCLE' => 'CardService.ImageStyle.CIRCLE'
  ];

  function __construct($value)
  {
    $this->setValue($value);
  }

  function setValue($value)
  {
    assert(in_array($value, self::values));
    $this->value = $value;
  }

  function build(): string
  {
    return self::values[$this->value];
  }
}

class CardServiceCardSection extends CardServiceBuildable
{
  private $header = null; // ?string
  private $widgets = []; // [CardServiceWidget]

  private function __construct(){}

  static function newCardSection(): CardServiceCardSection
  {
    return new self;
  }

  function setHeader(?string $header): CardServiceCardSection
  {
    $this->header = $header;
    return $this;
  }

  function addWidget(CardServiceWidget $widget): CardServiceCardSection
  {
    $this->widgets[] = $widget;
    return $this;
  }

  function build(): string
  {
    $retval = 'CardService.newCardSection()';
    if (isset($this->header)) {
      $retval .= '.setHeader(' . json_encode($this->header) . ')';
    }
    foreach ($this->widgets as $widget) {
      $retval .= '.addWidget(' . $widget->build() . ')';
    }
    return $retval;
  }
}

abstract class CardServiceWidget extends CardServiceBuildable
{
}

// A widget that displays text and supports basic HTML formatting.
// https://developers.google.com/apps-script/reference/card-service/text-paragraph
class CardServiceTextParagraph extends CardServiceWidget
{
  private $text = null; // ?string

  private function __construct(){}

  static function newTextParagraph(): CardServiceTextParagraph
  {
    return new self;
  }

  function setText(?string $text): CardServiceTextParagraph
  {
    $this->text = $text;
    return $this;
  }

  function build(): string
  {
    $retval = 'CardService.newTextParagraph()';
    if (isset($this->text)) {
      $retval .= '.setText(' . json_encode($this->text) . ')';
    }
    return $retval;
  }
}

// A widget that displays text and supports basic HTML formatting.
// https://developers.google.com/apps-script/reference/card-service/key-value
class CardServiceKeyValue extends CardServiceWidget
{
  private $topLabel = null; // ?string
  private $content = null; // ?string
  private $iconUrl = null; // ?string
  private $multiline = null; // ?bool

  private function __construct(){}

  static function newKeyValue(): CardServiceKeyValue
  {
    return new self;
  }

  // Sets the label text to be used as the key. Displayed above the text-content and supports basic HTML formatting.
  // https://developers.google.com/apps-script/reference/card-service/key-value#setTopLabel(String)
  function setTopLabel(?string $topLabel): CardServiceKeyValue
  {
    $this->topLabel = $topLabel;
    return $this;
  }

  // Sets the text to be used as the value. Supports basic HTML formatting.
  // https://developers.google.com/apps-script/reference/card-service/key-value#setContent(String)
  function setContent(?string $content): CardServiceKeyValue
  {
    $this->content = $content;
    return $this;
  }

  // Sets the URL of the icon to be used as the key.
  // https://developers.google.com/apps-script/reference/card-service/key-value#setIconUrl(String)
  function setIconUrl(?string $url): CardServiceKeyValue
  {
    $this->iconUrl = $url;
    return $this;
  }

  // Sets whether the value text should be displayed on a single line or multiple lines.
  // https://developers.google.com/apps-script/reference/card-service/key-value#setMultiline(Boolean)
  function setMultiline(?bool $multiline): CardServiceKeyValue
  {
    $this->multiline = $multiline;
    return $this;
  }

  function build(): string
  {
    $retval = 'CardService.newKeyValue()';
    if (isset($this->topLabel)) {
      $retval .= '.setTopLabel(' . json_encode($this->topLabel) . ')';
    }
    if (isset($this->content)) {
      $retval .= '.setContent(' . json_encode($this->content) . ')';
    }
    if (isset($this->iconUrl)) {
      $retval .= '.setIconUrl(' . json_encode($this->iconUrl) . ')';
    }
    if (isset($this->multiline)) {
      $retval .= '.setMultiline(' . json_encode($this->multiline) . ')';
    }
    return $retval;
  }
}

// Holds a set of Button objects that are displayed in a row.
// https://developers.google.com/apps-script/reference/card-service/button-set
class CardServiceButtonSet extends CardServiceWidget
{
  private $buttons = []; // [CardServiceButton]

  private function __construct(){}

  static function newButtonSet(): CardServiceButtonSet
  {
    return new self;
  }

  // Adds a button.
  // https://developers.google.com/apps-script/reference/card-service/button-set#addButton(Button)
  function addButton(CardServiceButton $button): CardServiceButtonSet
  {
    $this->buttons[] = $button;
    return $this;
  }

  function build(): string
  {
    $retval = 'CardService.newButtonSet()';
    foreach ($this->buttons as $button) {
      $retval .= '.addButton(' . $button->build() . ')';
    }
    return $retval;
  }
}

// A base class for all buttons.
// https://developers.google.com/apps-script/reference/card-service/button
abstract class CardServiceButton extends CardServiceWidget
{
  // should probably move the common Button functions into here. Have fun with that.
}

// A TextButton with a text label.
// https://developers.google.com/apps-script/reference/card-service/text-button
class CardServiceTextButton extends CardServiceButton
{
  private $text = null; // ?string
  private $openLink = null; // ?CardServiceOpenLink

  private function __construct(){}

  static function newTextButton(): CardServiceTextButton
  {
    return new self;
  }

  // Sets the text to be displayed on the button.
  // https://developers.google.com/apps-script/reference/card-service/text-button#setText(String)
  function setText(?string $text): CardServiceTextButton
  {
    $this->text = $text;
    return $this;
  }

  // Sets a URL to be opened when the object is clicked. Use this function when the URL is already known and only needs to be opened. A UI object can only have one of setOpenLink(openLink), setOnClickAction(action), setOnClickOpenLinkAction(action), setAuthorizationAction(action), or setComposeAction(action, composedEmailType) set.
  // https://developers.google.com/apps-script/reference/card-service/text-button#setOpenLink(OpenLink)
  function setOpenLink(CardServiceOpenLink $openLink): CardServiceTextButton
  {
    $this->openLink = $openLink;
    return $this;
  }

  function build(): string
  {
    $retval = 'CardService.newTextButton()';
    if (isset($this->text)) {
      $retval .= '.setText(' . json_encode($this->text) . ')';
    }
    if (isset($this->openLink)) {
      $retval .= '.setOpenLink(' . $this->openLink->build() . ')';
    }
    return $retval;
  }
}

// Represents an action to open a link with some options.
// https://developers.google.com/apps-script/reference/card-service/open-link
class CardServiceOpenLink extends CardServiceBuildable
{
  private $url = null; // ?string
  private $openAs = null; // ?CardServiceOpenAs

  private function __construct(){}

  static function newOpenLink(): CardServiceOpenLink
  {
    return new self;
  }

  // Sets the URL to be opened. The URL must match a prefix whitelisted in the manifest.
  // https://developers.google.com/apps-script/reference/card-service/open-link#setUrl(String)
  function setUrl(?string $url): CardServiceOpenLink
  {
    $this->url = $url;
    return $this;
  }

  // Sets the behavior of URL when it is opened.
  // https://developers.google.com/apps-script/reference/card-service/open-link#setOpenAs(OpenAs)
  function setOpenAs(CardServiceOpenAs $openAs): CardServiceOpenLink
  {
    $this->openAs = $openAs;
    return $this;
  }

  function build(): string
  {
    $retval = 'CardService.newOpenLink()';
    if (isset($this->url)) {
      $retval .= '.setUrl(' . json_encode($this->url) . ')';
    }
    if (isset($this->openAs)) {
      $retval .= '.setOpenAs(' . $this->openAs->build() . ')';
    }
    return $retval;
  }
}

class CardServiceOpenAs extends CardServiceBuildable {
  private $value;
  const values = [
    'FULL_SIZE' => 'CardService.OpenAs.FULL_SIZE',
    'OVERLAY' => 'CardService.OpenAs.OVERLAY'
  ];

  function __construct($value)
  {
    $this->setValue($value);
  }

  function setValue($value)
  {
    assert(in_array($value, self::values));
    $this->value = $value;
  }

  function build(): string
  {
    return self::values[$this->value];
  }
}
