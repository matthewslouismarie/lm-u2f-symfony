<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <script src="node_modules/jquery/dist/jquery.min.js"></script>
    <script src="assets/u2f-api.js"></script>
    <script>
      const completeRegistration = function fCompleteRegistration(registerResponse)
      {
        console.log(registerResponse);
        $('#challenge').val(JSON.stringify(registerResponse));
      }
      var request = <?= $request ?>;
      var sign_requests = <?= $sign_requests ?>;
      u2f.register([request], sign_requests, completeRegistration);
    </script>
  </head>
  <body>
      <form action="#" method="post">
        <input id="challenge" name="challenge" readonly type="text">
      </form>
  </body>
</html>