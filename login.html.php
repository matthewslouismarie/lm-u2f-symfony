<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <script src="node_modules/jquery/dist/jquery.min.js"></script>
    <script src="assets/u2f-api.js"></script>
    <script>
      const completeAuth = function fCompleteAuth(authResponse)
      {
        console.log(authResponse);
        $('#response').val(JSON.stringify(authResponse));
      }
      var sign_requests = <?= json_encode($sign_requests) ?>;
      u2f.sign(sign_requests, completeAuth);
    </script>
  </head>
  <body>
    <form action="#" method="post">
      <input id="response" name="response" type="text">
      <button type="submit">Submit</button>
    </form>
  </body>
</html>