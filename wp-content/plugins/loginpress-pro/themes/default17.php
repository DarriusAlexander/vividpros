
<?php
function seventeenth_presets() {
  ob_start();
  ?>
  <style media="screen" id="loginpress-style">
  html, body.login {
    height: auto !important;
  }
    body.login {
      /*background-image: url(<?php //echo plugins_url( 'img/v27_bg.jpg', LOGINPRESS_PLUGIN_BASENAME )  ?>);*/
      /*background-position: right bottom !important;*/
      background-color: #c1c1c1 !important;
      /*background-size: cover;*/
      display: table;
      min-height: 100vh;
      width: 100%;
      padding: 0;
      position: relative;
    }
    body.login.login-action-login{
      display: table  !important;
    }
    /*body.login:after{
      width: 100%;
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      height: 60%;
      background: #263466;
    }*/
    /*.login label{
    font-size:0;
    line-height:0;
    margin-top: 0;
    display: block;
    margin-bottom:
    }*/
    .login label{
      font-size: 16px;
      color: #404040;
      position: relative;
      display: block;
    }
    #login{
      background: url(<?php echo plugins_url( 'img/bg17.jpg', LOGINPRESS_PRO_PLUGIN_BASENAME )  ?>) no-repeat 0 0;
      /*background-size: cover !important;*/
      max-width: 913px !important;
      width: calc(100% - 100px) !important;
      margin: 60px auto 0;
      padding: 40px 20px 50px;
      position: relative;
      height: calc(100vh - 160px);
      min-height: 590px;
      box-shadow: 2.5px 4.33px 50px 0px rgba( 0, 0, 0, .2 );
    }

    #loginform{
      margin: 0 auto;
      padding: 30px 0 0 !important;
    }
    #login:after{
      width: 205px;
      height: 100%;
      position: absolute;
      top: 0;
      right: 0;
      background: #bbbbbb;
      content: '';
    }
    #login form p + p:not(.forgetmenot){
      margin-top: 35px;
    }
    .login form .input, .login input[type=text]{
      display: block;
      color: #bbbaba;
      font-size: 16px;
      width:100%;
      border:0;
      height: 45px;
      padding: 0 15px;
      border-radius: 0;
      -webkit-box-shadow: none;
      box-shadow: none;
      background-color: transparent;
      margin-top: 10px !important;
      border-bottom: 1px solid #d9d9d9;
    }
    input:-webkit-autofill{
      transition: all 100000s ease-in-out 0s !important;
      transition-property: background-color, color !important;
    }
    .login form{
      background: none;
      padding: 0;
      box-shadow: none;
    }
    .login form br{
    display: none;
    }
    #login form p.submit{
      clear: both;
      padding-top: 35px;
    }
    .wp-core-ui #login  .button-primary{
      width:100% !important;
      display: block;
      float: none;
      background-color : #f04d68;
      background-image: url(<?php echo plugins_url( 'img/arrow_right.svg', LOGINPRESS_PRO_PLUGIN_BASENAME )  ?>);
      background-position: center center;
      background-repeat: no-repeat;
      font-weight: 700;
      font-size: 0;
      line-height: 0;
      color : #ffffff;
      height: 56px;
      border:0;
      border-radius: 50%;
      box-shadow: 0px 8px 20px 0px rgba( 0, 0, 0, .15 );
      width: 50px !important;
      height: 50px;
      display: block;
      white-space: nowrap;
      overflow: hidden;
      padding: 0;
      text-indent: 100%;
      position: absolute;
      right: 50px;
      bottom: -25px;
    }
    .login form{
      overflow: visible;
    }

    .login form .forgetmenot label:after{
      visibility: hidden;
    }

    .wp-core-ui #login  .button-primary:hover{
      background-color: #fa2548;
    }
    .login form .forgetmenot label{
      font-size: 13px;
      color: #404040;
    }
    .login form input[type=checkbox]{
      border: 1px solid #404040;
      background: none;
      height: 13px;
      width: 13px;
      min-width: 13px;
    }
    .login #nav{
      font-size: 0;
      float: right;
      width: 100%;
    }
    .login #nav, .login #backtoblog {
      margin: 17px 0 0;
      padding: 0;
      color: #404040;
    }
    .login #nav a, .login #backtoblog a{
      font-size: 13px;
      color: #404040;
    }
    .login #nav a:first-child{
      float: left;
    }
    .login #nav a:last-child{
      float: right;
    }
    .login #backtoblog{
      float: left;
    }
    .login #backtoblog a:hover, .login #nav a:hover, .login h1 a:hover{
      color: #000;
    }
    .footer-wrapper{
    	display: table-footer-group;
    }
    .footer-cont{

    	right: 0;
    	bottom: 0;
    	left: 0;
    	text-align: center;
    	display: table-cell;
    	vertical-align: bottom;
    	height: 100px;
    }
    .copyRight{
    	text-align: center;
      padding: 12px;
      background-color: #f04d68;
      color: #ffffff;
    }
    #login form p + p:not(.forgetmenot){
    color: #d5d5d5;
    padding: 0;
    }
    .login-container:after{
      content: '';
      display: table;
      clear: both;
    }
    .login-container{
      position: absolute;
      right: 80px;
      top: 50%;
      transform: translateY(-50%);
      -webkit-transform: translateY(-50%);
      -mos-transform: translateY(-50%);
      -ms-transform: translateY(-50%);
      background: #fff;
      z-index: 1;
      width: 360px;
      padding: 30px;
      z-index: 2;
    }
    input[type=checkbox]:checked:before{
      font-size: 18px;
      color: #233849 !important;
    }
    .loginpress-show-love{
      color: #fff;
    }
    .loginpress-show-love a{
      color: #fff;
    }
    .loginpress-show-love a:hover{
      color: #fff;
    }
    .mobile #login{
      padding: 15px;
    }
    #login{
      min-height: none;
      height: auto;
    }
    @media screen and (max-width: 768px) {
       #login{
        padding: 15px 15px 0;
        float: none;
        margin: 20px auto 40px;
        width: 100% !important;
      }
      .mobile #login{
        padding: 15px;
        float: none;
        margin: 20px auto;
      }
      .login-container {
          position: static;
          transform: translateY(0);
          -webkit-transform: translateY(0);
          -mos-transform: translateY(0);
          -ms-transform: translateY(0);
          background: #fff;
          z-index: 1;
          max-width: 360px;
          width: calc(100% - 30px);
          padding: 15px 15px 40px;
          margin: 0 auto;
      }
      #login:after{
        visibility: hidden;
      }
    }
    @media screen and (max-width: 767px) {

      #login:after{
        visibility: hidden;
        display: none;
      }
      .login-container{
        width: 270px;
      }
      .login .loginpress-show-love{
        position: static;
        text-align: center;
        float: none;
        clear: both;
        background: rgba(240, 77, 104, 0.7215686274509804);
        padding: 3px 15px;
      }
      .login .loginpress-show-love{
        position: static;
        padding: 3px 15px;
        text-align: center;
        float: none;
      }
    }

    </style>
    <!-- <script src="https://code.jquery.com/jquery-1.12.4.min.js" integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ=" crossorigin="anonymous"></script> -->
    <script type="text/javascript">
      (function($){
        $(window).load(function(){
          // $("#login").wrapInner("<div class='login-container'></div>");
          $('#login').children('*').not('#loginpress_video-background-wrapper').wrapAll('<div class="login-container" />');

        });
      })(jQuery);
    </script>

  <?php
  $content = ob_get_clean();
  return $content;
}
echo seventeenth_presets();
