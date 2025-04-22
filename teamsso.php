<!DOCTYPE html>
<html>
<head>
  <title>Teams SSO PHP Test</title>
  <script src="https://res.cdn.office.net/teams-js/2.12.0/js/MicrosoftTeams.min.js"></script>
  <style>
    body { font-family: Arial, sans-serif; padding: 20px; }
  </style>
</head>
<body>

<h2>Teams SSO Token Test (PHP)</h2>
<div id="output">Initializing...</div>

<script>
microsoftTeams.app.initialize().then(() => {
  document.getElementById("output").innerText = "Teams SDK Initialized. Getting token...";

  microsoftTeams.authentication.getAuthToken({
    successCallback: function(token) {
      document.getElementById("output").innerText = "Token received. Sending to PHP...";

      // Send token to PHP via POST
      fetch("handler.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        body: "token=" + encodeURIComponent(token)
      })
      .then(response => response.json())
      .then(data => {
        document.getElementById("output").innerText = "Hello, " + data.name + " (" + data.email + ")";
      })
      .catch(error => {
        document.getElementById("output").innerText = "Failed to verify token: " + error;
      });
    },
    failureCallback: function(error) {
      document.getElementById("output").innerText = "Token request failed: " + error;
    }
  });
});
</script>

</body>
</html>
