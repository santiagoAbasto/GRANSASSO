<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'Admin') — Gran Sasso</title>
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Segoe UI', system-ui, sans-serif; background: #F3F4F6; color: #111; min-height: 100vh; }
a { color: #136CBA; text-decoration: none; }
a:hover { text-decoration: underline; }

/* NAV */
.admin-nav { background: #101010; padding: 0 32px; display: flex; align-items: center; gap: 32px; height: 56px; }
.admin-nav__brand { color: #fff; font-weight: 700; font-size: 16px; letter-spacing: .02em; }
.admin-nav__link { color: #ccc; font-size: 14px; }
.admin-nav__link:hover { color: #fff; text-decoration: none; }
.admin-nav__link.active { color: #fff; font-weight: 600; }

/* MAIN */
.admin-wrap { max-width: 1200px; margin: 40px auto; padding: 0 24px; }

/* PAGE HEADER */
.admin-page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 28px; }
.admin-page-title { font-size: 22px; font-weight: 700; }

/* CARD */
.admin-card { background: #fff; border-radius: 12px; border: 1px solid #E5E7EB; overflow: hidden; }

/* TABLE */
.admin-table { width: 100%; border-collapse: collapse; font-size: 14px; }
.admin-table thead tr { background: #F9FAFB; border-bottom: 1px solid #E5E7EB; }
.admin-table th { padding: 12px 16px; text-align: left; font-weight: 600; color: #555; font-size: 12px; text-transform: uppercase; letter-spacing: .04em; }
.admin-table td { padding: 12px 16px; border-bottom: 1px solid #F3F4F6; vertical-align: middle; }
.admin-table tr:last-child td { border-bottom: none; }
.admin-table tr:hover td { background: #FAFAFA; }

/* GROUP HEADER ROW */
.admin-table .group-header td { background: #EEF5FF; color: #136CBA; font-weight: 700; font-size: 13px; padding: 8px 16px; }

/* BADGE */
.badge { display: inline-flex; padding: 2px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
.badge--text   { background: #E8F4FF; color: #136CBA; }
.badge--number { background: #FFF3E0; color: #E65100; }
.badge--select { background: #F3E8FF; color: #7C3AED; }

/* BUTTONS */
.btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 18px; border-radius: 8px; font-size: 14px; font-weight: 500; border: none; cursor: pointer; transition: opacity .2s; }
.btn:hover { opacity: .85; text-decoration: none; }
.btn--primary  { background: #136CBA; color: #fff; }
.btn--danger   { background: #FEE2E2; color: #B91C1C; }
.btn--ghost    { background: #F3F4F6; color: #374151; }

/* FORM */
.admin-form { padding: 28px 32px; display: flex; flex-direction: column; gap: 20px; max-width: 640px; }
.form-field { display: flex; flex-direction: column; gap: 6px; }
.form-field label { font-size: 14px; font-weight: 600; color: #374151; }
.form-field input,
.form-field select { border: 1px solid #D1D5DB; border-radius: 8px; padding: 9px 14px; font-size: 14px; outline: none; transition: border-color .2s; width: 100%; }
.form-field input:focus,
.form-field select:focus { border-color: #136CBA; }
.form-field small { color: #888; font-size: 12px; }
.form-actions { display: flex; gap: 12px; padding-top: 4px; }

/* ALERTS */
.admin-alert { padding: 12px 18px; border-radius: 8px; font-size: 14px; margin-bottom: 20px; }
.admin-alert--success { background: #ECFDF5; border: 1px solid #10B981; color: #065F46; }
.admin-alert--error   { background: #FEF2F2; border: 1px solid #EF4444; color: #991B1B; }
.admin-alert--error ul { padding-left: 16px; }
</style>
</head>
<body>

<nav class="admin-nav">
    <span class="admin-nav__brand">Gran Sasso Admin</span>
    <a href="{{ route('admin.technical-fields.index') }}"
       class="admin-nav__link {{ request()->routeIs('admin.technical-fields*') ? 'active' : '' }}">
        Campos Técnicos
    </a>
    <a href="{{ route('admin.dashboard') }}" class="admin-nav__link">Dashboard</a>
</nav>

<div class="admin-wrap">

    @if(session('success'))
    <div class="admin-alert admin-alert--success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
    <div class="admin-alert admin-alert--error">
        <ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    @yield('content')

</div>

</body>
</html>
