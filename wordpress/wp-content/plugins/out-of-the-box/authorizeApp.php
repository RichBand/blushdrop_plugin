<?php
session_start();

// Load Dropbox SDK
try {
  require_once "includes/dropbox-sdk/Dropbox/autoload.php";
} catch (Exception $ex) {
  return new WP_Error('broke', __('Couldn\'t load Dropbox SDK libary'));
}

use \Dropbox as dbx;

class authorizeApp {

  private $redirectUri = '';
  private $appInfo = array();
  private $error = '';

  public function __construct() {
    $this->redirectUri = $this->curPageURL();

    if (isset($_REQUEST['wp_redirect'])) {
      $testdata = (strtr($_REQUEST['wp_redirect'], '-_~', '+/='));

      if (base64_encode(base64_decode($testdata)) === $testdata) {
        $_SESSION['wp_redirect'] = base64_decode($testdata);
      } else {
        $_SESSION['wp_redirect'] = urldecode($_REQUEST['wp_redirect']);
      }
    }

    if (isset($_REQUEST['app_key']) && isset($_REQUEST['app_secret'])) {
      $this->startAuth();
    } else if (!empty($_GET['code'])) {
      $this->finishAuth();
    } else {
      $this->startScreen();
    }
  }

  private function startAuth() {
    if (empty($_REQUEST['app_key']) || empty($_REQUEST['app_secret'])) {
      $this->error = 'Set a valid key &amp; secret';
      $this->startScreen();
    }

    //Create array with key & secret
    $_appInfo = array('key' => $_REQUEST['app_key'], 'secret' => $_REQUEST['app_secret']);

    $this->setAppinfo($_appInfo);
    $authorizeUrl = $this->startWebAuth();
    header('location: ' . $authorizeUrl);
    die();
  }

  private function finishAuth() {
    $this->setAppinfo($_SESSION['appInfo']);
    $createToken = $this->createToken();

    if (isset($_SESSION['wp_redirect'])) {
      header('location: ' . $_SESSION['wp_redirect'] . '&_token=' . $this->accessToken);
      die();
    } else {
      $this->startScreen();
    }
  }

  private function setAppinfo($_appInfo) {
    //Check key & secret
    try {
      $appInfo = dbx\AppInfo::loadFromJson($_appInfo);
    } catch (dbx\AppInfoLoadException $ex) {
      $this->error = ("Unable to load app-info.") . $ex->getMessage();
      $this->startScreen();
    }
    $clientIdentifier = "Out-of-the-Box-Authorizer(Wordpress)/1.0";
    $userLocale = 'en';

    //set complete AppInfo
    $this->appInfo = array('appInfo' => $appInfo, 'clientIdentifier' => $clientIdentifier, 'userLocale' => $userLocale);

    $_SESSION['appInfo'] = $_appInfo;
  }

  /*
   * OAuth 2 "authorization code" flow
   */

  function getWebAuth() {
    $csrfTokenStore = new dbx\ArrayEntryStore($_SESSION, 'dropbox-auth-csrf-token');
    return new dbx\WebAuth($this->appInfo['appInfo'], $this->appInfo['clientIdentifier'], $this->redirectUri, $csrfTokenStore);
  }

  /*
   * Gets a $authorizeUrl
   *
   * @return string
   * The URL to redirect the user to.
   */

  public function startWebAuth() {
    try {
      $authorizeUrl = $this->getWebAuth()->start();
    } catch (Exception $ex) {
      $this->error = "/dropbox-auth-start: could not start authorization: " . $ex->getMessage();
      $this->startScreen();
    }
    return $authorizeUrl;
  }

  /*
   * Creates token after the user has visited the authorize URL, approved the app,
   * and was redirected to your redirect URI.
   *
   * @return WP_Error|true
   */

  public function createToken() {
    try {
      list($accessToken, $userId, $urlState) = $this->getWebAuth()->finish($_GET);
      $this->accessToken = $accessToken;
    } catch (dbx\WebAuthException_BadRequest $ex) {
      $this->error = "/dropbox-auth-finish: bad request: " . $ex->getMessage();
      $this->startScreen();
    } catch (dbx\WebAuthException_BadState $ex) {
      // Auth session expired.  Restart the auth process.
      header('Location: ' . $this->redirectUri);
      die();
    } catch (dbx\WebAuthException_Csrf $ex) {
      $this->error = "/dropbox-auth-finish: CSRF mismatch: " . $ex->getMessage();
      $this->startScreen();
    } catch (dbx\WebAuthException_NotApproved $ex) {
      $this->error = "/dropbox-auth-finish: not approved: " . $ex->getMessage();
      $this->startScreen();
    } catch (dbx\WebAuthException_Provider $ex) {
      $this->error = "/dropbox-auth-finish: error redirect from Dropbox: " . $ex->getMessage();
      $this->startScreen();
    } catch (dbx\Exception $ex) {
      $this->error = "/dropbox-auth-finish: error communicating with Dropbox API: " . $ex->getMessage();
      $this->startScreen();
    }


    return true;
  }

  public function startScreen() {
    ?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml">
      <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Dropbox Token Creator<?php echo isset($this->accessToken) ? ' - token created' : ''; ?></title>
        <script type="text/javascript">
          var _gaq = _gaq || [];
          _gaq.push(['_setAccount', 'UA-4471306-1']);
          _gaq.push(['_trackPageview']);

          (function() {
            var ga = document.createElement('script');
            ga.type = 'text/javascript';
            ga.async = true;
            ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
            var s = document.getElementsByTagName('script')[0];
            s.parentNode.insertBefore(ga, s);
          })();
        </script>
        <style type="text/css">
          @charset "UTF-8";
          body{
            font-size:1.1em;
            font-family:Verdana, Arial, sans-serif;
            line-height:1.6;
            color: #666;
            margin-bottom: 10px;
            text-align: center;
          }

          div{
            word-wrap: break-word;
          }

          div.form{
            background-color: #efefef;
            width:400px;
            margin: 0 auto;
            padding:30px;
            border: 1px solid #bbb;
          }

          div.error{
            padding:10px;
            margin-bottom: 10px;
            border:red;
            background-color: lightcoral;
            font-weight: bold;
            font-size: 80%;
            color:black;
          }
          div.logo {
            margin: 0 auto;
            width: 228px;
            height: 107px;
            background-image: url('data:image/png;base64,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');
          }
          input[type="text"]{
            padding:5px;
            font-size: 110%;
            border: 1px solid #BBB;
          }
          .button {
            padding: 5px 10px;
            display: inline;
            background: #777;
            border: none;
            color: #fff;
            cursor: pointer;
            font-weight: bold;
            font-size: 120%;
            border-radius: 5px;
            -moz-border-radius: 5px;
            -webkit-border-radius: 5px;
            text-shadow: 1px 1px #666;
          }
          .button:hover {
            background-position: 0 -48px;
          }
          .button:active {
            background-position: 0 top;
            position: relative;
            top: 1px;
            padding: 6px 10px 4px;
          }
          li {
            margin-bottom:5px;
          }
          .small{
            font-size: 70%;
          }
        </style>
      </head>
      <body>
        <div class="form">
          <div class="logo"></div>

          <?php
          if ($this->error !== '') {
            ?>
            <div class='error'><?php echo $this->error; ?></div>
            <?php
          }
          if (isset($this->accessToken)) {
            ?>
            <div class='success'>Your token: <?php echo $this->accessToken; ?></div>
            <p>
              <em class='small'>Remove our URL from the App console after you got your token.</em>
            </p>
            <?php
          } else {
            ?>
            <p class='small'>Add <br/><input type="text" name="app_url" id="app_url" value="<?php echo $this->curPageURL(); ?>" onclick="this.select();" style="width:100%;"/><br/> to your <strong>OAuth redirect URIs</strong> in the <a href="https://www.dropbox.com/developers/apps/" target="_blank">App Console</a></p>
            <form id="token_creator" name="token_creator" method="post" action="">
              <p>
                <label for="app_key">Your Dropbox App key</label>
                <input type="text" name="app_key" id="app_key" />
                <br/>
                <label for="app_secret">Your Dropbox App secret</label>
                <input type="text" name="app_secret" id="app_secret" />
                <br/><br/>
                <input type="submit" name="button" id="button" value="Grab your Token!" class='button' />
              </p>
              <p>
                <em class='small'>1.<br/>
                  Yes, you get a SSL warning if you try to open this page. But because you don't mind using Out-of-the-Box without a SSL certificate, just ignore it.
                  <br/><br/>2.<br/>
                  We don't save your data or share it. This script just simply creates a redirect with your key and secret to Dropbox and shows the created Token.
                </em>
              </p>
            <?php }; ?>
          </form>
        </div>
      </body>
    </html>
    <?php
    die();
  }

  function curPageURL() {
    $pageURL = 'http';
    if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
      $pageURL .= "s";
    }
    $pageURL .= "://";
    if ($_SERVER["SERVER_PORT"] != "80") {
      $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["PHP_SELF"];
    } else {
      $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["PHP_SELF"];
    }
    return $pageURL;
  }

}

$authorizeApp = new authorizeApp;