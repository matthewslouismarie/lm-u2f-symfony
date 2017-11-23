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
      var request = <?= $request_json ?>;
      var sign_requests = <?= $sign_requests ?>;
      u2f.register([request], sign_requests, completeRegistration);
    </script>
  </head>
  <body>
      <form action="#" method="post">
        <input id="username" name="username" type="text" required>
        <input id="request" name="request" readonly type="text" value="<?= htmlspecialchars($request_json, ENT_COMPAT, 'UTF-8', false) ?>">
        <input id="challenge" name="challenge" readonly type="text">
        <input id="reg-id" name="reg-id" type="text" value="<?= htmlspecialchars($reg_id) ?>">
        <button type="submit">Register</button>
      </form>
  </body>
</html>