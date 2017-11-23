<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <script src="node_modules/jquery/dist/jquery.min.js"></script>
    <script src="assets/u2f-api.js"></script>
    <script>
      const completeAuth = function fCompleteRegistration(registerResponse)
      {
        console.log(registerResponse);
        $('#challenge').val(JSON.stringify(registerResponse));
      }
      var sign_requests = <?= json_encode($sign_requests) ?>;
      u2f.sign([sign_requests], completeAuth);
    </script>
  </head>
  <body>
  </body>
</html>