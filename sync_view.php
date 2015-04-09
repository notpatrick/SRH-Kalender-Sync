<div class="starter-template starter-width">
  <div class="page-header">
    <h1>Google Calendar sync</h1>
  </div>
  <div class="panel panel-primary">
    <div class="panel-heading">Login</div>
    <div class="panel-body">
      <form action="sync_exec.php" method="post">
        <div class="form-group">
          <label for="name">Matrikelnummer</label>
          <input type="text" class="form-control" placeholder="Matrikelnummer" name="name">
        </div>
        <div class="form-group">
          <label for="password">Passwort</label>
          <input type="password" class="form-control" placeholder="Passwort" name="password">
        </div>
        <div class="form-group">
          <label for="calendar-id">Kalender-ID</label>
          <input type="text" class="form-control" placeholder="Kalender-ID" name="calendar-id">
        </div>
        <button type="submit" class="btn btn-primary">Sync!</button>
      </form>
    </div>
  </div>
  <br>
  <form action="sync_google_login.php" method="post">
    <button type="submit" class="btn btn-primary">Google Login</button>
  </form>
  <br>
  <form action="sync_google_logout.php" method="post">
    <button type="submit" class="btn btn-danger">Google Logout</button>
  </form>
</div>