window.onerror = function(msg, url, linenumber){
  <?php if(ENVIRONMENT != "production") { ?>
  if(typeof tacoSpeech != "undefined") tacoSpeech('Hey! You have an error message: <b>'+msg+'</b>.<br>In: <b>'+url+'</b> <br>Line Number: <b>'+linenumber+'</b>', 10000);
  var filename = "Screen-" + Date.now() + ".png";
  kendo.drawing.drawDOM($("body"))
  .then((group) => {
    return kendo.drawing.exportImage(group);
  })
  .done((dataImg) => {
    $.ajax({
      url: ENV.vApi + "upload/capture",
      type: "POST",
      data: {dataImg: dataImg, filename: filename},
      success: function(response) {
        if(response.status) {
          var data = {
            title: "JS error",
            imgPath: response.filepath,
            url: window.location.href,
            priority: "High",
            content: 'Error message: '+msg+'. Url: '+url+'. Line Number: '+linenumber
          };
          $.ajax({
            url: ENV.restApi + "reporterror",
            type: "POST",
            contentType: "application/json; charset=utf-8",
              data: kendo.stringify(data)
          })
        }
      }
    });
  });
  <?php } else { ?>
  setTimeout(function() {
    swal({
        title: `@Some error occurred@`,
        text: `@Do you want to report@?`,
        icon: "warning",
        timer: 3000,
        buttons: true,
    })
    .then((sure) => {
        if (sure) {
            var filename = "Screen-" + Date.now() + ".png";
            kendo.drawing.drawDOM($("body"))
            .then((group) => {
              return kendo.drawing.exportImage(group);
            })
            .done((dataImg) => {
              $.ajax({
                url: ENV.vApi + "upload/capture",
                type: "POST",
                data: {dataImg: dataImg, filename: filename},
                success: function(response) {
                  if(response.status) {
                    var data = {
                      title: "JS error",
                      imgPath: response.filepath,
                      url: window.location.href,
                      priority: "High",
                      content: 'Error message: '+msg+'. Url: '+url+'. Line Number: '+linenumber
                    };
                    $.ajax({
                      url: ENV.restApi + "reporterror",
                      type: "POST",
                      contentType: "application/json; charset=utf-8",
                        data: kendo.stringify(data)
                    })
                  }
                }
              });
            }); 
        }
    });
    return true;
  }, 3000);
  <?php } ?>
}

function actionPhoneRing(ele) {
    if(ENV.short_key_ipphone) {
      swal({
          title: "@Action@",
          text: `@What do you want to do with this call@?`,
          icon: "warning",
          buttons: {
              cancel: "@Cancel@",
              no: {
                text: "@Reject@",
                value: "CALLEND",
                className: "swal-button--danger",
              },
              ok: {
                text: "@Answer@",
                value: "ANSWER",
                className: "swal-button--success",
              },
          }
      })
      .then((value) => {
          if(value) {
            $.get(ENV.vApi + "ipphone/press", {key: value}, function(res) {notification.show(res.message, res.status ? "success" : "error")});
          }
      })
    }
}

function copyToClipboard(value) {
    if(typeof value == "object") {
        value = value.textContent
    }
    var input = document.createElement("input");
    document.body.appendChild(input);
    input.value = value;
    input.select();
    document.execCommand("copy");
    document.body.removeChild(input);
    document.body.dataset.clipboard = value;
    notification.show("@Copied@ @value@: <b>" + value + "</b>", "success");
}


function playSound(type) {
    if(ENV.sound_effect) {
      var url = "<?= STEL_PATH ?>media/";
      switch (type) {
          case "dial":
              url += "dial.mp3";
              break;
          case "btn-click": default:
              url += "btn-click.mp3"; 
              break;
      }
      $("#sound-contain").html(`<audio autoplay>
            <source src="${url}" type="audio/mpeg">
          Your browser does not support the audio element.
          </audio>`);
    }
}

async function translateForm(text) {
  openForm({title: "@Translate@", width: 400})
  $rightForm = $("#right-form");
  var formHtml = await $.ajax({
      url: ENV.templateApi + "translate/form",
      data: {text: text},
      type: "POST",
      error: errorDataSource
  });
  kendo.destroy($rightForm);
  $rightForm.empty();
  $rightForm.append(formHtml);
}