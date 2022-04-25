/**
 * Returns the array of cards that should be rendered for the current
 * e-mail thread. The name of this function is specified in the
 * manifest 'onTriggerFunction' field, indicating that this function
 * runs every time the add-on is started.
 *
 * @param {Object} e data provided by the Gmail UI.
 * @returns {Card[]}
 */
function buildCards(e) {
  // Activate temporary Gmail add-on scopes.
  const accessToken = e.messageMetadata.accessToken;
  GmailApp.setCurrentMessageAccessToken(accessToken);

  const messageId = e.messageMetadata.messageId;
  const mail = GmailApp.getMessageById(messageId);
  const thread = mail.getThread();
  var send_data = {
    'messageFrom': mail.getFrom(),
    'messageTo': mail.getTo(),
    'messageCc': mail.getCc(),
    'messageBcc': mail.getBcc(),
    'messagePlainBody': mail.getPlainBody(),
    'messageSubject': mail.getSubject(),
    'threadLabels': thread.getLabels().map(function(l){return l.getName()})
  };
  const javascriptToMakeCards = authenticateAndFetch(send_data);
  
  Logger.log(javascriptToMakeCards);
  const cards = function(){return eval(javascriptToMakeCards);}();
  return cards;
}

/**
 * Builds and executes a JSON REST request
 * Uses WEBHOOK_URL and HMAC_SHA_256_KEY from script properties
 *
 * @param {Object} payload Details to send to server
 * @return {Object} Text response from server
 */
function authenticateAndFetch(payload) {
  const endpoint = PropertiesService.getScriptProperties().getProperty('WEBHOOK_URL');
  const hmac_key = PropertiesService.getScriptProperties().getProperty('HMAC_SHA_256_KEY');
  var send_data = JSON.stringify(payload);
  send_data = Utilities.base64Encode(send_data); // https://stackoverflow.com/q/51866469/300224
  const signature = Utilities.computeHmacSignature(Utilities.MacAlgorithm.HMAC_SHA_256, send_data, hmac_key)
    .map(function(chr){return (chr+256).toString(16).slice(-2)})
    .join(''); // Golf https://stackoverflow.com/a/49759368/300224

  const fetch_options = {
    'method': 'post',
    'payload': {'gmail_details': send_data, 'signature': signature},
    'muteHttpExceptions': true
  };
  const response = UrlFetchApp.fetch(endpoint, fetch_options);
  return response.getContentText();
}