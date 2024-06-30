
<h2>Login</h2>

<?php if (isset($error)) { ?>
<div class="error">
    <?php echo $error; ?>
</div>
<?php } ?>

<div class="login-form">
    <form method="POST" action="?page=login">
        <input type="text" name="username" placeholder="Username" required />
        <br />
        <input type="password" name="password" placeholder="Password" required />
        <br />
        <label for="remember_me">
            <input type="checkbox" id="remember_me" name="remember_me" />
            remember me
        </label>
        <br />
        <input type="submit" value="Login" />
    </form>
</div>
