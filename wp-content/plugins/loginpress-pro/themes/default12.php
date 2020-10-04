
<?php
function twelfth_presets() {
  ob_start();
  ?>
  <style media="screen" id="loginpress-style">
  html, body.login {
    height: auto !important;
  }
    body.login {
      background-image: url(<?php echo plugins_url( 'img/bg12.jpg', LOGINPRESS_PRO_PLUGIN_BASENAME )  ?>);
      background-position: center center;
      /*background-color: #f1f1f1 !important;*/
      background-size: cover;
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
      color: #cccccc;
      position: relative;
    }
    #login{
      /*background: url(img/form_bg.jpg) no-repeat 0 0 !important;*/
      background: none;
      max-width: 350px !important;
      width: 100% !important;
      border-radius: 10px;
      margin-top: 8%;
      padding: 40px 20px 50px;
      float: left;
      margin-left: 8%;
    }
    #loginform{
      margin: 0 auto;
      padding: 30px 0 0 !important;
    }
    #login:after{
      content: '';
      display: table;
      clear: both;
    }
    #login form p + p:not(.forgetmenot){
      margin-top: 35px;
    }
    .login form .input, .login input[type=text]{
      display: block;
      color: #909090;
      font-size: 16px;
      width:100%;
      border:0;
      height: 45px;
      padding: 0 15px;
      border-radius: 3px;
      -webkit-box-shadow: none;
      box-shadow: none;
      padding-left: 80px;
      background-color: rgba(255,255, 255, .85);
      margin-top: 10px !important;
      border: 1px solid #e4e4e3;
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
      background-color : #f8011c;
      font-weight: 700;
      font-size: 18px;
      color : #ffffff;
      height: 56px;
      border:0;
      box-shadow: none;
      border-radius: 3px;
      box-shadow: 0px 5px 20px 0px rgb( 255, 255, 255 , .20);
    }
    .login label:after{
      width: 60px;
      height: 45px;
      background-color: rgba(0,0,0, .05);
      position: absolute;
      bottom: 16px;
      left: 0;
      border-radius: 0 0 5px 5px;
      content: '';
    }
    .login label[for="user_login"]:after,
    .login label[for="user_pass"]:after{
      background-image: url(<?php echo plugins_url( 'img/login-field-icons.svg', LOGINPRESS_PLUGIN_BASENAME )  ?>);
    }
    .login label[for="user_login"]:after{
      background-repeat: no-repeat;
      background-position: 0 center;
    }
    .login label[for="user_pass"]:after{
      background-repeat: no-repeat;
      background-position: -60px center;
    }
    .login form .forgetmenot label:after{
      visibility: hidden;
    }

    .wp-core-ui #login  .button-primary:hover{
      background-color: #f12239;
    }
    .login form .forgetmenot label{
      font-size: 13px;
      color: #cccccc;
    }
    .login form input[type=checkbox]{
      background: none;
      border: 1px solid #e5e4e2;
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
      color: #cccccc;
    }
    .login #nav a, .login #backtoblog a{
      font-size: 13px;
      color: #cccccc;
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
      color: #eae8e8;
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
      background-color: #f8011c;
      color: #ffffff;
    }
    #login form p + p:not(.forgetmenot){
    color: #d5d5d5;
    }
    input[type=checkbox]:checked:before{
      font-size: 18px;
      color: #f8011c !important;
    }
    .loginpress-show-love{
      color: #fff;
    }
    .loginpress-show-love a{
      color: #eae8e8;
    }
    .loginpress-show-love a:hover{
      color: #fff;
    }
    .mobile #login{
      padding: 15px;
    }
    .login label{
      display: block;
    }
    .login label:after{
      bottom: 0;
    }
    @media screen and (max-width: 768px) {
       #login{
        padding: 15px;
        float: none;
        margin: 20px auto;
        width: 290px !important;
      }
      .mobile #login{
        padding: 15px;
        float: none;
        margin: 20px auto;
      }
    }

    </style>

  <?php
  $content = ob_get_clean();
  return $content;
}
echo twelfth_presets();
