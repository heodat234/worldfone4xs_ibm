<div class="taco-container" onclick="tacoPrompt()">
    <img src="<?= STEL_PATH ?>img/taco.png" ?>
    <div class="taco-speech-bubble">Hello, I'm Taco assistant.<br>This is <?= ENVIRONMENT ?> environment.</div>
</div>
<script type="text/javascript">
    function tacoPrompt(context = "") {
        let text = (context || 'Can i help you?');
        tacoRead(text);
        swal({
          text: text,
          icon: "<?= STEL_PATH ?>img/taco-reading.png",
          content: "input",
          button: {
            text: "Send!",
            closeModal: true,
          },
        })
        .then(message => {
          if(message == null) return;
          var messageArr = message.split(" ");
          switch(messageArr[0].toLowerCase()) {
          	case "omc":
              tacoRead("Let's go to only main contain mode");
              setTimeout(() => {
                location.href = "<?= fix_current_url() ?>?omc=1";
              }, 2000)
          		break;
          	case "profiler":
              tacoRead("You will see profiler this page now");
              setTimeout(() => {
          		  location.href = "<?= fix_current_url() ?>?cmd_profiler=show";
              }, 2000)
          		break;
            case "phplog": case "phplogs": case "php_logs":
              tacoRead("Now you will see php log");
              setTimeout(() => {
                if(typeof messageArr[1] != 'undefined')
                  location.href = "<?= fix_current_url() ?>?cmd_php_logs=" +  messageArr[1];
                else location.href = "<?= fix_current_url() ?>?cmd_php_logs=today";
              }, 2000)
              break;
          	case "default": case "origin":
          		location.href = "<?= fix_current_url() ?>";
          		break;
          	case "hello": case "hi":
          		tacoPrompt("Hello! I'm Taco. Please type your message.");
          		break;
          	default:
	          	tacoPrompt("Sorry! I can't understand your message \""+message+"\".");
          		break;
          }
        })
    }
    function tacoRead(text = "") {
      let speech = new SpeechSynthesisUtterance(text);
      window.speechSynthesis.speak(speech);
    }
    function tacoSpeech(text = "", timeOut = 3000) {
      $tacoSpeechContain = $(".taco-container");
      $tacoSpeechSelect = $(".taco-speech-bubble");
      window.originTacoSpeech = $tacoSpeechSelect.html();
      $tacoSpeechSelect.html(text);
      $tacoSpeechContain.addClass("show-speech");
      setTimeout(() => {
        $tacoSpeechContain.removeClass("show-speech");
        $tacoSpeechSelect.html(window.originTacoSpeech);
      }, timeOut);
    }
</script>
<style type="text/css">
    .taco-container {
        position: fixed; 
        bottom: -50px; 
        left: 60px; 
        cursor: pointer;
        z-index: 9999;
    }
    .taco-container:hover,
    .taco-container.show-speech {
        bottom: -10px;
    }
    .taco-container:hover .taco-speech-bubble,
    .taco-container.show-speech .taco-speech-bubble {
        display: inline-block;
    }
    .taco-speech-bubble{
        background-color: #F2F2F2;
        border-radius: 5px;
        box-shadow: 0 0 6px #B2B2B2;
        padding: 10px 18px;
        position: relative;
        vertical-align: top;
        display: none;
    }

    .taco-speech-bubble::before {
        background-color: #F2F2F2;
        content: "\00a0";
        display: block;
        height: 16px;
        position: absolute;
        top: 14px;
        transform:             rotate( 29deg ) skew( -35deg );
            -moz-transform:    rotate( 29deg ) skew( -35deg );
            -ms-transform:     rotate( 29deg ) skew( -35deg );
            -o-transform:      rotate( 29deg ) skew( -35deg );
            -webkit-transform: rotate( 29deg ) skew( -35deg );
        width:  20px;

        box-shadow: -2px 2px 2px 0 rgba( 178, 178, 178, .4 );
        left: -9px;
    }
</style>