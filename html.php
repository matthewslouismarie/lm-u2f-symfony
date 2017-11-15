<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <script src="assets/u2f-api.js"></script>
    <script>
      const completeRegistration = function fCompleteRegistration(registerResponse)
      {
        console.log(registerResponse);
      }
      var request = <?= $request ?>;
      var sign_requests = <?= $sign_requests ?>;
      u2f.register([request], sign_requests, completeRegistration);
    </script>
  </head>
  <body>
      <form action="#" method="post">
        <input name="challenge" readonly type="text">
      </form>
  </body>
</html>