<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - QMS</title>
  <link rel="stylesheet" href="{{ asset('qms-assets/style.css') }}">
  <link rel="stylesheet" href="{{ asset('qms-assets/phase2.css') }}">
</head>
<body class="login-body">
  <main class="login-shell">
    <section class="login-hero">
      <p class="eyebrow">QMS.ysaidea.com</p>
      <h1>Quality, Safety, Risk and Improvement</h1>
      <p>One workspace for reporting, observations, HSE review, actions, investigations, CAPA, assurance and management visibility.</p>
    </section>
    <form class="login-card" method="POST" action="{{ route('login.store') }}">
      @csrf
      <h2>Sign in</h2>
      @error('email')<div class="form-error">{{ $message }}</div>@enderror
      <label>Email<input name="email" type="email" value="{{ old('email', 'admin@qms.test') }}" required autofocus></label>
      <label>Password<input name="password" type="password" value="password" required></label>
      <label class="inline-check"><input type="checkbox" name="remember" value="1"> Remember me</label>
      <button class="primary-button full">Enter QMS</button>
      <div class="demo-users">
        <strong>Demo users</strong>
        <span>admin@qms.test / password</span>
        <span>yahya.alnaaimi@qms.test / Yahya@2026</span>
        <span>mazin.alfarsi@qms.test / Mazin@2026</span>
      </div>
    </form>
  </main>
</body>
</html>
