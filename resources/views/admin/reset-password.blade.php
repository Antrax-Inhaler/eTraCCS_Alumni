<form method="POST" action="{{ route('admin.password.update') }}">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">
    <input type="email" name="email" value="{{ request('email') }}" readonly>
    <input type="password" name="password" placeholder="New Password">
    <input type="password" name="password_confirmation" placeholder="Confirm Password">
    <button type="submit">Reset Password</button>
</form>
