
<?php
function eighteen_presets() {
  ob_start();
  ?>
  <style media="screen" id="loginpress-style">
  html, body.login {
    height: auto !important;
  }
  body.login {
    background-image: url(<?php echo plugins_url( 'img/bg4.jpg', LOGINPRESS_PRO_PLUGIN_BASENAME )  ?>);
    background-position: center center;
    /*background-color: #f1f1f1 !important;*/
    background-size: cover;
    display: table !important;
    min-height: 100vh;
    width: 100%;
    padding: 0;
    position: relative;
  }
  body.login.login-action-login{
    display: table  !important;
  }
  .login #login h1 a{
    width: 200px !important;
    height: 201px !important;
    background-image: url(<?php echo plugins_url( 'img/all_sprites.png', LOGINPRESS_PRO_PLUGIN_BASENAME )  ?>) !important;
    background-size: auto !important;
    background-repeat: no-repeat;
        -webkit-animation: eyeblink 1.5s steps(23) infinite;
       -moz-animation: eyeblink 1.5s steps(23) infinite;
        -ms-animation: eyeblink 1.5s steps(23) infinite;
         -o-animation: eyeblink 1.5s steps(23) infinite;
            animation: eyeblink 1.5s steps(23) infinite;
  }
  @keyframes eyeblink {
      from { background-position: 0px   0px; }
      to { background-position: -4600px 0px; }
  }
  .login #login h1 a.watchdown{
        -webkit-animation: watchdown .8s steps(18) 1 forwards;
       -moz-animation: watchdown .8s steps(18) 1 forwards;
        -ms-animation: watchdown .8s steps(18) 1 forwards;
         -o-animation: watchdown .8s steps(18) 1 forwards;
            animation: watchdown .8s steps(18) 1 forwards;
    }
    @keyframes watchdown {
        from { background-position:    0px -402px; }
        to { background-position: -3600px -402px; }
    }
  .login #login h1 a.watchup{
    -webkit-animation: watchup .8s steps(18) 1;
       -moz-animation: watchup .8s steps(18) 1;
        -ms-animation: watchup .8s steps(18) 1;
         -o-animation: watchup .8s steps(18) 1;
            animation: watchup .8s steps(18) 1;
    }
    @keyframes watchup {
      from { background-position:    -3400px -402px; }
        to { background-position: -7000px -402px; }
    }
    .login #login h1 a.yeti-hide[data-state="pwfocus"]{
        -webkit-animation: hideeye .5s steps(10) 1 forwards;
       -moz-animation: hideeye .5s steps(10) 1 forwards;
        -ms-animation: hideeye .5s steps(10) 1 forwards;
         -o-animation: hideeye .5s steps(10) 1 forwards;
            animation: hideeye .5s steps(10) 1 forwards;
    }
    @keyframes hideeye {
        from { background-position:    0px -201px; }
        to { background-position: -2000px -201px; }
    }
    .login #login h1 a.yeti-seak{
        -webkit-animation: seak .5s steps(10) 1;
       -moz-animation: seak .5s steps(10) 1;
        -ms-animation: seak .5s steps(10) 1;
         -o-animation: seak .5s steps(10) 1;
            animation: seak .5s steps(10) 1;
    }
    @keyframes seak {
        from { background-position:    -2000px -201px; }
        to { background-position: -4000px -201px; }
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
    color: #909090;
  }
  #login{
    background: #fff !important;
    padding: 40px 30px 40px;
    max-width: 390px;
    width: calc(100% - 30px) !important;
    border-radius: 10px;
    box-shadow: 0px 5px 50px 0px rgba(0, 0, 0, 0.1);
    margin-top: 4%;
  }
  .mobile #login{
    padding-right: 15px;
    padding-left: 15px;
  }
  #login:after{
    content: '';
    display: table;
    clear: both;
  }

  #loginform{
    margin: 0;
    padding: 0 !important;
  }
  #login form p + p:not(.forgetmenot){
    margin-top: 35px;
  }
  .login form .input, .login input[type=text]{
    background: #fff;
    display: block;
    color: #909090;
    font-size: 16px;
    /*font-family: 'Open Sans';*/
    width:100%;
    border:0;
    height: 50px;
    padding: 0 15px;
    border:1px solid #909090;
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
    background-color : #263466;
    font-weight: 700;
    font-size: 18px;
    /*font-family: 'Open Sans';*/
    color : #ffffff;
    height: 56px;
    border-radius: 0;
    border:0;
    box-shadow: none;
  }
  .wp-core-ui #login  .button-primary:hover{
    background-color: #4d5d95;
  }
  .login form .forgetmenot label{
    font-size: 13px;
    /*font-family: 'Open Sans';*/
    color: #b2b2b2;
  }
  .login form input[type=checkbox]{
    background: none;
    border: 1px solid #d5d5d5;
    height: 13px;
    width: 13px;
    min-width: 13px;
  }
  .login #nav, .login #backtoblog {
    margin: 17px 0 0;
    padding: 0;
    font-size: 12px;
    /*font-family: "Open Sans";*/
    color: #b2b2b2;
  }
  .login #nav a, .login #backtoblog a{
    font-size: 12px;
    /*font-family: "Open Sans";*/
    color: #b2b2b2;
  }
  .login #backtoblog{
    float: left;
  }
  .login #nav {
    font-size: 0;
    float: right;
    width: 100%;
  }
  .login #nav a:last-child {
    float: right;
  }
  .login #nav a:first-child {
    float: left;
  }
  .login #backtoblog{
    float: left;
  }
  .login #backtoblog a:hover, .login #nav a:hover, .login h1 a:hover{
    color: #222;
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
    background-color: #263466;
    color: #ffffff;
  }
  #login form p + p:not(.forgetmenot){
    color: #d5d5d5;
  }
  input[type=checkbox]:checked:before{
    font-size: 18px;
  }
  .loginpress-show-love{
    color: #a0aeef;
  }
  .loginpress-show-love a{
    color: #8e8efc;
  }
  .loginpress-show-love a:hover{
    color: #8e8efc;
  }
  @media screen and (max-width: 767px) {
      .login .loginpress-show-love{
        position: static;
        float: none;
        text-align: center;
        padding: 3px;
      }
      .login #login{
        max-width: 300px;
      }
  }

  </style>
  <script language="Javascript" type="text/javascript">
    // (function($){
    //   $(document).ready(function(){
    //     $('#loginform #user_login').on('focus',function(){
    //       $('.login h1 a').attr('data-state', 'uifocus');
    //       $('.login h1 a').addClass('watchdown');
    //     });
    //     $('#loginform #user_login').on('blur',function(){
    //         $('.login h1 a').attr('data-state', 'uiblur');
    //         $('.login h1 a').removeClass('watchdown').addClass('watchup');
    //             setTimeout( function() {
    //         $('.login h1 a').removeClass('watchup');
    //             }, 800);
    //     });
    //     $('#loginform #user_pass').on('focus',function(){
    //             $('.login h1 a').attr('data-state', 'pwfocus');
    //             setTimeout( function() {
    //             $('.login h1 a').addClass('yeti-hide');
    //             }, 800);
    //     });
    //     $('#loginform #user_pass').on('blur',function(){
    //             $('.login h1 a').attr('data-state', 'pwblur');
    //             $('.login h1 a').removeClass('yeti-hide').addClass('yeti-seak');
    //             setTimeout( function() {
    //             $('.login h1 a').removeClass('yeti-seak');
    //             }, 500);
    //     });
    //   });
    // }(jQuery));
    function temp18(){
      var loginpress_avatar = document.querySelector('.login h1 a');
      document.getElementById('user_login').addEventListener("focus", function(){
        loginpress_avatar.setAttribute('data-state', 'uifocus');
        loginpress_avatar.classList.add('watchdown');
      }, false);
      document.getElementById('user_login').addEventListener("blur", function(){
        loginpress_avatar.setAttribute('data-state', 'uiblur');
        loginpress_avatar.classList.remove('watchdown');
        loginpress_avatar.classList.add('watchup');
        setTimeout( function() {
          loginpress_avatar.classList.remove('watchup');
        },800);
      }, false);
      document.getElementById('user_pass').addEventListener("focus", function(){
        loginpress_avatar.setAttribute('data-state', 'pwfocus');
        setTimeout( function() {
          loginpress_avatar.classList.add('yeti-hide');
        },800);
      }, false);
      document.getElementById('user_pass').addEventListener("blur", function(){
        loginpress_avatar.setAttribute('data-state', 'pwblur');
        // loginpress_avatar.removeClass('yeti-hide').addClass('yeti-seak');
        loginpress_avatar.classList.remove('yeti-hide');
        loginpress_avatar.classList.add('yeti-seak');
        setTimeout( function() {
        loginpress_avatar.classList.remove('yeti-seak');
        },800);
      }, false);
    }
    window.addEventListener ? window.addEventListener( "load", temp18, false ) : window.attachEvent && window.attachEvent( "onload", temp18 );


  </script>
  <?php
  $content = ob_get_clean();
  return $content;
}
echo eighteen_presets();
