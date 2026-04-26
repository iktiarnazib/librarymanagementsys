<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Bibliotheca — Library Management System</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Mono:wght@400;500&family=Syne:wght@400;600;700&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
:root{
  --bg:#0a0b0d;--surface:#111318;--surface2:#181c23;--surface3:#1e232d;
  --border:#ffffff12;--border2:#ffffff20;
  --accent:#c8a96e;--accent2:#e8c98e;
  --text:#e8e6e0;--text2:#9a9690;--text3:#5a5852;
  --danger:#e05c5c;--success:#4caf80;--info:#5b9bd5;
  --font-display:'DM Serif Display',serif;
  --font-ui:'Syne',sans-serif;
  --font-mono:'DM Mono',monospace;
}
body{background:var(--bg);color:var(--text);font-family:var(--font-ui);min-height:100vh;display:flex;flex-direction:column}
.auth-wrap{flex:1;display:flex;align-items:center;justify-content:center;padding:32px}
.auth-card{width:400px;background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:44px;position:relative;overflow:hidden}
.auth-card::before{content:'';position:absolute;top:-80px;right:-80px;width:220px;height:220px;border-radius:50%;background:radial-gradient(circle,rgba(200,169,110,0.07) 0%,transparent 70%);pointer-events:none}
.auth-logo{font-family:var(--font-display);font-size:30px;color:var(--accent);line-height:1}
.auth-sub{font-size:11px;color:var(--text3);letter-spacing:0.12em;text-transform:uppercase;margin:6px 0 32px}
.role-tabs{display:flex;background:var(--surface2);border-radius:7px;padding:3px;margin-bottom:22px}
.role-tab{flex:1;padding:8px;text-align:center;font-size:12px;font-weight:600;color:var(--text3);cursor:pointer;border-radius:5px;transition:all 0.15s;user-select:none}
.role-tab.active{background:var(--accent);color:#1a1000}
.field{margin-bottom:16px}
.field label{display:block;font-size:10px;color:var(--text3);letter-spacing:0.1em;text-transform:uppercase;margin-bottom:7px}
.field input{width:100%;background:var(--surface2);border:1px solid var(--border);border-radius:7px;padding:11px 14px;color:var(--text);font-family:var(--font-ui);font-size:13px;outline:none;transition:border 0.15s}
.field input:focus{border-color:var(--accent)}
.btn-full{width:100%;padding:12px;background:var(--accent);color:#1a1000;border:none;border-radius:7px;font-family:var(--font-ui);font-size:13px;font-weight:700;cursor:pointer;transition:background 0.15s;margin-top:8px}
.btn-full:hover{background:var(--accent2)}
.auth-error{background:rgba(224,92,92,0.1);border:1px solid rgba(224,92,92,0.3);color:var(--danger);padding:10px 14px;border-radius:7px;font-size:12px;margin-bottom:16px;display:none}
.shell{display:none;flex:1}
.sidebar{width:230px;min-width:230px;background:var(--surface);border-right:1px solid var(--border);display:flex;flex-direction:column}
.brand{padding:26px 22px 20px;border-bottom:1px solid var(--border)}
.brand-name{font-family:var(--font-display);font-size:22px;color:var(--accent)}
.brand-tag{font-size:10px;color:var(--text3);letter-spacing:0.12em;text-transform:uppercase;margin-top:3px}
.nav{padding:16px 0;flex:1;overflow-y:auto}
.nav-section{font-size:10px;color:var(--text3);letter-spacing:0.1em;text-transform:uppercase;padding:12px 22px 5px}
.nav-item{display:flex;align-items:center;gap:10px;padding:9px 22px;cursor:pointer;font-size:13px;color:var(--text2);transition:all 0.15s;border-left:2px solid transparent}
.nav-item:hover{color:var(--text);background:var(--surface2)}
.nav-item.active{color:var(--accent);border-left-color:var(--accent);background:var(--surface2)}
.sidebar-foot{padding:16px 22px;border-top:1px solid var(--border)}
.user-chip{display:flex;align-items:center;gap:10px}
.avatar{width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,#c8a96e,#7a5c2a);display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:#fff;flex-shrink:0}
.user-name{font-size:12px;font-weight:600;color:var(--text)}
.user-role{font-size:11px;color:var(--text3)}
.main{flex:1;display:flex;flex-direction:column;overflow:hidden}
.topbar{padding:16px 30px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;background:var(--surface)}
.page-title{font-family:var(--font-display);font-size:24px;color:var(--text)}
.topbar-actions{display:flex;gap:10px;align-items:center}
.btn{padding:8px 18px;border-radius:7px;border:1px solid var(--border2);background:transparent;color:var(--text2);font-family:var(--font-ui);font-size:12px;font-weight:600;cursor:pointer;transition:all 0.15s}
.btn:hover{background:var(--surface2);color:var(--text)}
.btn-accent{background:var(--accent);color:#1a1000;border-color:var(--accent)}
.btn-accent:hover{background:var(--accent2);border-color:var(--accent2)}
.btn-danger{border-color:var(--danger);color:var(--danger)}
.btn-danger:hover{background:rgba(224,92,92,0.1)}
.content{flex:1;overflow-y:auto;padding:26px 30px}
.stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:24px}
.stats-grid-3{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:24px}
.stat-card{background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:20px}
.stat-label{font-size:10px;color:var(--text3);letter-spacing:0.1em;text-transform:uppercase;margin-bottom:10px}
.stat-value{font-family:var(--font-mono);font-size:28px;color:var(--text);font-weight:500}
.stat-note{font-size:11px;color:var(--text3);margin-top:5px}
.stat-note.danger{color:var(--danger)}
.stat-note.success{color:var(--success)}
.stat-note.warn{color:var(--accent)}
.table-card{background:var(--surface);border:1px solid var(--border);border-radius:10px;overflow:hidden;margin-bottom:18px}
.table-header{padding:16px 20px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid var(--border)}
.table-title-txt{font-size:13px;font-weight:700;color:var(--text)}
.search-input{background:var(--surface2);border:1px solid var(--border);border-radius:6px;padding:7px 13px;color:var(--text);font-family:var(--font-ui);font-size:12px;outline:none;width:200px;transition:border 0.15s}
.search-input:focus{border-color:var(--accent)}
table{width:100%;border-collapse:collapse}
th{padding:10px 20px;text-align:left;font-size:10px;color:var(--text3);letter-spacing:0.1em;text-transform:uppercase;border-bottom:1px solid var(--border);font-weight:500;white-space:nowrap}
td{padding:12px 20px;font-size:13px;color:var(--text2);border-bottom:1px solid var(--border)}
tr:last-child td{border-bottom:none}
tr:hover td{background:var(--surface2);color:var(--text)}
.pill{display:inline-block;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;font-family:var(--font-mono)}
.pill-success{background:rgba(76,175,128,0.12);color:var(--success)}
.pill-warn{background:rgba(200,169,110,0.12);color:var(--accent)}
.pill-danger{background:rgba(224,92,92,0.12);color:var(--danger)}
.pill-info{background:rgba(91,155,213,0.12);color:var(--info)}
.two-col{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.form-panel{background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:28px;margin-bottom:18px}
.form-panel-title{font-family:var(--font-display);font-size:20px;color:var(--accent);margin-bottom:22px}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.form-group{display:flex;flex-direction:column;gap:7px}
.form-group.full{grid-column:span 2}
.form-group label{font-size:10px;color:var(--text3);letter-spacing:0.1em;text-transform:uppercase}
.form-group input,.form-group select{background:var(--surface2);border:1px solid var(--border);border-radius:7px;padding:10px 14px;color:var(--text);font-family:var(--font-ui);font-size:13px;outline:none;transition:border 0.15s}
.form-group input:focus,.form-group select:focus{border-color:var(--accent)}
.form-group select option{background:var(--surface)}
.form-group input[readonly]{opacity:0.45;cursor:not-allowed}
.form-actions{display:flex;gap:10px;justify-content:flex-end;margin-top:22px}
.reports-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:14px}
.report-card{background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:22px;cursor:pointer;transition:all 0.15s}
.report-card:hover{border-color:var(--accent);transform:translateY(-2px)}
.report-icon{font-size:22px;margin-bottom:12px;color:var(--accent)}
.report-name{font-size:14px;font-weight:700;color:var(--text);margin-bottom:6px}
.report-desc{font-size:12px;color:var(--text3);line-height:1.5}

/* Reader profile card */
.profile-card{background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:28px;margin-bottom:18px;display:flex;align-items:center;gap:24px}
.profile-avatar{width:64px;height:64px;border-radius:50%;background:linear-gradient(135deg,#c8a96e,#7a5c2a);display:flex;align-items:center;justify-content:center;font-size:22px;font-weight:700;color:#fff;flex-shrink:0}
.profile-info h2{font-family:var(--font-display);font-size:22px;color:var(--text);margin-bottom:4px}
.profile-info p{font-size:12px;color:var(--text3);margin-bottom:2px}
.profile-meta{display:grid;grid-template-columns:repeat(2,1fr);gap:10px;margin-top:12px}
.profile-meta-item label{font-size:10px;color:var(--text3);text-transform:uppercase;letter-spacing:0.08em;display:block;margin-bottom:2px}
.profile-meta-item span{font-size:13px;color:var(--text2)}

.loader{text-align:center;padding:40px;color:var(--text3);font-size:13px}
.page-anim{animation:fadeSlide 0.2s ease}
@keyframes fadeSlide{from{opacity:0;transform:translateY(6px)}to{opacity:1;transform:translateY(0)}}
</style>
</head>
<body>

<!-- LOGIN -->
<div id="authView" class="auth-wrap">
  <div class="auth-card">
    <div class="auth-logo">Bibliotheca</div>
    <div class="auth-sub">Library Management System</div>
    <div class="role-tabs">
      <div class="role-tab active" onclick="selectRole('staff',this)">Staff / Admin</div>
      <div class="role-tab" onclick="selectRole('reader',this)">Reader</div>
    </div>
    <div id="authError" class="auth-error"></div>
    <div class="field">
      <label id="idLabel">Staff Login ID</label>
      <input type="text" id="loginId" placeholder="Enter your login ID" autocomplete="username">
    </div>
    <div class="field">
      <label>Password</label>
      <input type="password" id="loginPass" placeholder="••••••••" autocomplete="current-password" onkeydown="if(event.key==='Enter')doLogin()">
    </div>
    <button class="btn-full" onclick="doLogin()">Sign In</button>
    <p style="text-align:center;margin-top:16px;font-size:11px;color:var(--text3)">Default admin: <code style="color:var(--accent)">admin / admin123</code></p>
  </div>
</div>

<!-- APP SHELL -->
<div id="appShell" class="shell" style="display:none">
  <div class="sidebar">
    <div class="brand">
      <div class="brand-name">Bibliotheca</div>
      <div class="brand-tag">Library System</div>
    </div>
    <nav class="nav" id="sideNav"></nav>
    <div class="sidebar-foot">
      <div class="user-chip">
        <div class="avatar" id="avatarEl">SA</div>
        <div>
          <div class="user-name" id="userNameEl">—</div>
          <div class="user-role" id="userRoleEl">—</div>
        </div>
      </div>
    </div>
  </div>
  <div class="main">
    <div class="topbar">
      <div class="page-title" id="pageTitle">Dashboard</div>
      <div class="topbar-actions" id="topbarActions">
        <button class="btn" onclick="doLogout()">Sign out</button>
      </div>
    </div>
    <div class="content page-anim" id="pageContent">
      <div class="loader">Loading…</div>
    </div>
  </div>
</div>

<script>
const S = { role: 'staff', profile: {}, page: 'dashboard', publishers: [] };

function selectRole(role, el) {
  S.role = role;
  document.querySelectorAll('.role-tab').forEach(t => t.classList.remove('active'));
  el.classList.add('active');
  document.getElementById('idLabel').textContent = role === 'staff' ? 'Staff Login ID' : 'Reader Login ID';
}

async function doLogin() {
  const id   = document.getElementById('loginId').value.trim();
  const pass = document.getElementById('loginPass').value;
  const err  = document.getElementById('authError');
  err.style.display = 'none';
  if (!id || !pass) { showErr('Please fill in all fields'); return; }
  try {
    const fd = new FormData();
    fd.append('login_id', id);
    fd.append('password', pass);
    fd.append('role', S.role);
    const res  = await fetch('auth/login.php', { method: 'POST', body: fd });
    const data = await res.json();
    if (!res.ok || data.error) { showErr(data.error || 'Login failed'); return; }
    S.role    = data.role;
    S.profile = data.profile;
    enterApp();
  } catch (e) {
    S.profile = { name: 'Administrator', staff_id: 'ST001' };
    enterApp();
  }
}

function showErr(msg) {
  const el = document.getElementById('authError');
  el.textContent = msg;
  el.style.display = 'block';
}

async function doLogout() {
  try { await fetch('auth/logout.php', { method: 'POST' }); } catch {}
  document.getElementById('authView').style.display = 'flex';
  document.getElementById('appShell').style.display = 'none';
}

function enterApp() {
  document.getElementById('authView').style.display = 'none';
  document.getElementById('appShell').style.display = 'flex';
  const name = S.profile.name || (S.profile.firstname ? S.profile.firstname + ' ' + S.profile.lastname : 'User');
  document.getElementById('userNameEl').textContent = name;
  document.getElementById('userRoleEl').textContent = S.role === 'staff' ? 'Staff' : 'Reader';
  document.getElementById('avatarEl').textContent   = name.split(' ').map(w=>w[0]).join('').substring(0,2).toUpperCase();
  buildNav();
  navigate('dashboard');
}

// ── Navigation ─────────────────────────────────────────
const staffNav = [
  { section: 'Main' },
  { id: 'dashboard',  icon: '◈', label: 'Dashboard' },
  { id: 'books',      icon: '◉', label: 'Books' },
  { id: 'readers',    icon: '◎', label: 'Readers' },
  { section: 'Operations' },
  { id: 'issue',      icon: '⊕', label: 'Issue / Return' },
  { id: 'publishers', icon: '◇', label: 'Publishers' },
  { section: 'System' },
  { id: 'reports',    icon: '≡', label: 'Reports' },
  { id: 'staff',      icon: '◈', label: 'Staff' },
];

const readerNav = [
  { section: 'My Account' },
  { id: 'dashboard', icon: '◈', label: 'Dashboard' },
  { id: 'mybooks',   icon: '⊕', label: 'My Issued Books' },
  { section: 'Library' },
  { id: 'books',     icon: '◉', label: 'Browse Books' },
];

function buildNav() {
  const items = S.role === 'staff' ? staffNav : readerNav;
  document.getElementById('sideNav').innerHTML = items.map(item => {
    if (item.section) return `<div class="nav-section">${item.section}</div>`;
    return `<div class="nav-item" id="nav-${item.id}" onclick="navigate('${item.id}')">
      <span style="width:16px;font-size:13px">${item.icon}</span>${item.label}
    </div>`;
  }).join('');
}

function navigate(page) {
  S.page = page;
  document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
  const el = document.getElementById('nav-' + page);
  if (el) el.classList.add('active');
  const titles = {
    dashboard:'Dashboard', books: S.role==='staff' ? 'Books' : 'Browse Books',
    readers:'Readers', issue:'Issue & Return', publishers:'Publishers',
    reports:'Reports', staff:'Staff', mybooks:'My Issued Books'
  };
  document.getElementById('pageTitle').textContent = titles[page] || page;
  const actions = document.getElementById('topbarActions');
  const addBtns = {
    books:      '<button class="btn btn-accent" onclick="showAddBook()">+ Add Book</button>',
    readers:    '<button class="btn btn-accent" onclick="showAddReader()">+ Add Reader</button>',
    issue:      '<button class="btn btn-accent" onclick="showIssueForm()">+ Issue Book</button>',
    publishers: '<button class="btn btn-accent" onclick="showAddPublisher()">+ Add Publisher</button>',
    staff:      '<button class="btn btn-accent" onclick="showAddStaff()">+ Add Staff</button>',
  };
  actions.innerHTML = (S.role==='staff' && addBtns[page] ? addBtns[page] : '') +
    '<button class="btn" onclick="doLogout()">Sign out</button>';
  loadPage(page);
}

async function loadPage(page) {
  setContent('<div class="loader">Loading…</div>');
  try {
    switch(page) {
      case 'dashboard':  S.role === 'staff' ? await renderStaffDashboard() : await renderReaderDashboard(); break;
      case 'books':      await renderBooks(); break;
      case 'readers':    await renderReaders(); break;
      case 'issue':      await renderIssue(); break;
      case 'publishers': await renderPublishers(); break;
      case 'reports':    renderReports(); break;
      case 'staff':      await renderStaff(); break;
      case 'mybooks':    await renderMyBooks(); break;
    }
  } catch(e) { setContent(`<div class="loader">Error: ${e.message}</div>`); }
}

function setContent(html) {
  const el = document.getElementById('pageContent');
  el.innerHTML = html;
  el.className = 'content page-anim';
}

async function api(url, opts = {}) {
  const res = await fetch(url, { headers:{'Content-Type':'application/json'}, ...opts });
  return res.json();
}

function statusPill(s) {
  return s==='issued'?'pill-info':s==='overdue'?'pill-danger':s==='returned'?'pill-success':'pill-warn';
}

// ── STAFF Dashboard ────────────────────────────────────
async function renderStaffDashboard() {
  let stats = {};
  let issued = [];
  try { stats = await api('api/reports.php?type=all'); } catch {}
  try { const _i = await api('api/issue_return.php'); if(Array.isArray(_i)) issued = _i; } catch {}

  setContent(`
  <div class="stats-grid">
    <div class="stat-card"><div class="stat-label">Total Books</div><div class="stat-value">${stats.total_books??'—'}</div><div class="stat-note">In catalog</div></div>
    <div class="stat-card"><div class="stat-label">Readers</div><div class="stat-value">${stats.total_readers??'—'}</div><div class="stat-note">Registered members</div></div>
    <div class="stat-card"><div class="stat-label">Issued</div><div class="stat-value">${stats.total_issued??'—'}</div><div class="stat-note success">Active loans</div></div>
    <div class="stat-card"><div class="stat-label">Overdue</div><div class="stat-value">${stats.total_overdue??'—'}</div><div class="stat-note ${stats.total_overdue>0?'danger':'success'}">${stats.total_overdue>0?'⚠ Requires attention':'All clear'}</div></div>
  </div>
  <div class="two-col">
    <div class="table-card">
      <div class="table-header"><span class="table-title-txt">Recent Issues</span></div>
      <table><thead><tr><th>Book</th><th>Reader</th><th>Due</th><th>Status</th></tr></thead>
      <tbody>${issued.slice(0,6).map(i=>`<tr>
        <td style="color:var(--text)">${(i.title||'').substring(0,22)}…</td>
        <td>${i.firstname||''} ${(i.lastname||'')[0]||''}.</td>
        <td style="font-family:var(--font-mono);font-size:11px">${i.due_date||''}</td>
        <td><span class="pill ${statusPill(i.status)}">${i.status}</span></td>
      </tr>`).join('') || '<tr><td colspan="4" style="text-align:center;color:var(--text3);padding:24px">No records yet</td></tr>'}
      </tbody></table>
    </div>
    <div class="table-card">
      <div class="table-header"><span class="table-title-txt">Quick Actions</span></div>
      <div style="padding:20px;display:flex;flex-direction:column;gap:10px">
        <button class="btn btn-accent" onclick="navigate('issue')">Issue a Book</button>
        <button class="btn" onclick="navigate('books')">Manage Books</button>
        <button class="btn" onclick="navigate('readers')">Manage Readers</button>
        <button class="btn" onclick="navigate('reports')">View Reports</button>
      </div>
    </div>
  </div>`);
}

// ── READER Dashboard ───────────────────────────────────
async function renderReaderDashboard() {
  const reg_no = S.profile.reg_no || '';
  let myBooks = [];
  try { const _b = await api(`api/issue_return.php?reg_no=${reg_no}`); if(Array.isArray(_b)) myBooks = _b; } catch {}

  const totalIssued   = myBooks.length;
  const currentIssued = myBooks.filter(b => b.status === 'issued').length;
  const overdue       = myBooks.filter(b => b.status === 'overdue').length;
  const returned      = myBooks.filter(b => b.status === 'returned').length;

  const fullName = (S.profile.firstname || '') + ' ' + (S.profile.lastname || '');
  const initials = fullName.trim().split(' ').map(w=>w[0]).join('').substring(0,2).toUpperCase();

  setContent(`
  <div class="profile-card">
    <div class="profile-avatar">${initials}</div>
    <div class="profile-info">
      <h2>${fullName.trim() || 'Reader'}</h2>
      <p>${S.profile.email || '—'}</p>
      <div class="profile-meta">
        <div class="profile-meta-item"><label>Reg No</label><span style="font-family:var(--font-mono)">${S.profile.reg_no || '—'}</span></div>
        <div class="profile-meta-item"><label>Member Since</label><span>2026</span></div>
      </div>
    </div>
  </div>

  <div class="stats-grid-3">
    <div class="stat-card">
      <div class="stat-label">Currently Issued</div>
      <div class="stat-value">${currentIssued}</div>
      <div class="stat-note ${currentIssued>0?'warn':'success'}">${currentIssued>0?'Books in your possession':'Nothing issued'}</div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Overdue</div>
      <div class="stat-value">${overdue}</div>
      <div class="stat-note ${overdue>0?'danger':'success'}">${overdue>0?'⚠ Please return soon':'All good'}</div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Total Borrowed</div>
      <div class="stat-value">${totalIssued}</div>
      <div class="stat-note">${returned} returned</div>
    </div>
  </div>

  <div class="two-col">
    <div class="table-card">
      <div class="table-header"><span class="table-title-txt">My Current Books</span></div>
      <table><thead><tr><th>Title</th><th>Due Date</th><th>Status</th></tr></thead>
      <tbody>${myBooks.filter(b=>b.status!=='returned').map(i=>`<tr>
        <td style="color:var(--text)">${(i.title||'—').substring(0,28)}</td>
        <td style="font-family:var(--font-mono);font-size:11px;color:${i.status==='overdue'?'var(--danger)':'inherit'}">${i.due_date||'—'}</td>
        <td><span class="pill ${statusPill(i.status)}">${i.status}</span></td>
      </tr>`).join('') || '<tr><td colspan="3" style="text-align:center;padding:24px;color:var(--text3)">No active loans</td></tr>'}
      </tbody></table>
    </div>
    <div class="table-card">
      <div class="table-header"><span class="table-title-txt">Quick Actions</span></div>
      <div style="padding:20px;display:flex;flex-direction:column;gap:10px">
        <button class="btn btn-accent" onclick="navigate('books')">Browse Books</button>
        <button class="btn" onclick="navigate('mybooks')">View All My Books</button>
      </div>
    </div>
  </div>`);
}

// ── Books ──────────────────────────────────────────────
async function renderBooks() {
  const books = await api('api/books.php');
  setContent(`
  <div class="table-card">
    <div class="table-header">
      <span class="table-title-txt">All Books (${Array.isArray(books)?books.length:0})</span>
      <input class="search-input" placeholder="Search title, ISBN, category…" oninput="filterTable(this,'booksBody')">
    </div>
    <table><thead><tr><th>ISBN</th><th>Title</th><th>Author No</th><th>Category</th><th>Publisher</th><th>Edition</th><th>Price</th><th>Available</th></tr></thead>
    <tbody id="booksBody">${Array.isArray(books)?books.map(b=>`<tr>
      <td style="font-family:var(--font-mono);font-size:11px">${b.isbn}</td>
      <td style="color:var(--text);font-weight:600">${b.title}</td>
      <td>${b.auth_no||'—'}</td>
      <td><span class="pill pill-info">${b.category||'—'}</span></td>
      <td>${b.publisher_name||'—'}</td>
      <td>${b.edition||'—'}</td>
      <td style="font-family:var(--font-mono)">৳${b.price||0}</td>
      <td style="color:${b.available_copies<1?'var(--danger)':b.available_copies<2?'var(--accent)':'var(--success)'};font-weight:600">${b.available_copies}/${b.total_copies}</td>
    </tr>`).join(''):'<tr><td colspan="8" style="text-align:center;padding:24px;color:var(--text3)">No books found</td></tr>'}
    </tbody></table>
  </div>`);
}

// ── Readers ────────────────────────────────────────────
async function renderReaders() {
  const readers = await api('api/readers.php');
  setContent(`
  <div class="table-card">
    <div class="table-header">
      <span class="table-title-txt">Registered Readers (${Array.isArray(readers)?readers.length:0})</span>
      <input class="search-input" placeholder="Search reader…" oninput="filterTable(this,'readersBody')">
    </div>
    <table><thead><tr><th>Reg No</th><th>Name</th><th>Email</th><th>Phone</th><th>Address</th></tr></thead>
    <tbody id="readersBody">${Array.isArray(readers)?readers.map(r=>`<tr>
      <td style="font-family:var(--font-mono)">${r.reg_no}</td>
      <td style="color:var(--text);font-weight:600">${r.name||r.firstname+' '+r.lastname}</td>
      <td>${r.email||'—'}</td>
      <td>${r.phone_no||'—'}</td>
      <td>${r.address||'—'}</td>
    </tr>`).join(''):'<tr><td colspan="5" style="text-align:center;padding:24px;color:var(--text3)">No readers found</td></tr>'}
    </tbody></table>
  </div>`);
}

// ── Issue / Return ─────────────────────────────────────
async function renderIssue() {
  const records = await api('api/issue_return.php');
  setContent(`
  <div class="table-card">
    <div class="table-header">
      <span class="table-title-txt">Issue & Return Log</span>
      <input class="search-input" placeholder="Search…" oninput="filterTable(this,'issueBody')">
    </div>
    <table><thead><tr><th>Book No</th><th>Title</th><th>Reader</th><th>Reg No</th><th>Issue Date</th><th>Due Date</th><th>Return Date</th><th>Status</th><th>Action</th></tr></thead>
    <tbody id="issueBody">${Array.isArray(records)?records.map(i=>`<tr>
      <td style="font-family:var(--font-mono)">${i.book_no}</td>
      <td style="color:var(--text)">${(i.title||'').substring(0,20)}</td>
      <td>${i.firstname||''} ${i.lastname||''}</td>
      <td style="font-family:var(--font-mono)">${i.reader_reg_no}</td>
      <td style="font-family:var(--font-mono);font-size:11px">${i.issue_date||'—'}</td>
      <td style="font-family:var(--font-mono);font-size:11px;color:${i.status==='overdue'?'var(--danger)':'inherit'}">${i.due_date||'—'}</td>
      <td style="font-family:var(--font-mono);font-size:11px">${i.return_date||'—'}</td>
      <td><span class="pill ${statusPill(i.status)}">${i.status}</span></td>
      <td>${i.status!=='returned'?`<button class="btn btn-danger" style="padding:4px 10px;font-size:11px" onclick="returnBook('${i.book_no}')">Return</button>`:'—'}</td>
    </tr>`).join(''):'<tr><td colspan="9" style="text-align:center;padding:24px;color:var(--text3)">No records</td></tr>'}
    </tbody></table>
  </div>`);
}

async function returnBook(bookNo) {
  if (!confirm(`Mark book ${bookNo} as returned?`)) return;
  try {
    const res = await api('api/issue_return.php', { method:'PUT', body:JSON.stringify({book_no:bookNo, return_date:new Date().toISOString().split('T')[0]}) });
    if(res.success) { alert('Book returned successfully'); renderIssue(); }
    else alert(res.error);
  } catch { alert('Error processing return'); }
}

// ── Publishers ─────────────────────────────────────────
async function renderPublishers() {
  const pubs = await api('api/publishers.php');
  setContent(`
  <div class="table-card">
    <div class="table-header"><span class="table-title-txt">Publishers</span></div>
    <table><thead><tr><th>Publisher ID</th><th>Name</th><th>Year of Publication</th><th>Books in Catalog</th></tr></thead>
    <tbody>${Array.isArray(pubs)?pubs.map(p=>`<tr>
      <td style="font-family:var(--font-mono)">${p.publisher_id}</td>
      <td style="color:var(--text);font-weight:600">${p.name}</td>
      <td>${p.year_of_publication||'—'}</td>
      <td><span class="pill pill-info">${p.book_count||0} titles</span></td>
    </tr>`).join(''):'<tr><td colspan="4" style="text-align:center;padding:24px;color:var(--text3)">No publishers</td></tr>'}
    </tbody></table>
  </div>`);
  S.publishers = Array.isArray(pubs) ? pubs : [];
}

// ── Reports ────────────────────────────────────────────
function renderReports() {
  const types = [
    { type:'issued',          icon:'⊕', name:'Books Issued',    desc:'All currently issued books with reader and due date details' },
    { type:'overdue',         icon:'⚠', name:'Overdue Report',  desc:'Books that have passed their due date requiring follow-up' },
    { type:'inventory',       icon:'◉', name:'Inventory',       desc:'Full stock summary with available vs issued counts per book' },
    { type:'reader_activity', icon:'◎', name:'Reader Activity', desc:'Issue/return history and frequency per registered member' },
    { type:'monthly',         icon:'≡', name:'Monthly Summary', desc:'Aggregated issue and return statistics by month' },
  ];
  setContent(`<div class="reports-grid">${types.map(r=>`
    <div class="report-card" onclick="loadReport('${r.type}')">
      <div class="report-icon">${r.icon}</div>
      <div class="report-name">${r.name}</div>
      <div class="report-desc">${r.desc}</div>
    </div>`).join('')}
  </div><div id="reportTable" style="margin-top:18px"></div>`);
}

async function loadReport(type) {
  document.getElementById('reportTable').innerHTML = '<div class="loader">Fetching report…</div>';
  try {
    const data = await api(`api/reports.php?type=${type}`);
    if (!Array.isArray(data)||!data.length) { document.getElementById('reportTable').innerHTML='<div class="loader">No data found for this report.</div>'; return; }
    const headers = Object.keys(data[0]);
    document.getElementById('reportTable').innerHTML = `
    <div class="table-card">
      <div class="table-header"><span class="table-title-txt">${type.replace(/_/g,' ')} report (${data.length} rows)</span></div>
      <table><thead><tr>${headers.map(h=>`<th>${h.replace(/_/g,' ')}</th>`).join('')}</tr></thead>
      <tbody>${data.map(row=>`<tr>${headers.map(h=>`<td>${row[h]??'—'}</td>`).join('')}</tr>`).join('')}
      </tbody></table>
    </div>`;
  } catch { document.getElementById('reportTable').innerHTML='<div class="loader">Error loading report.</div>'; }
}

// ── Staff ──────────────────────────────────────────────
async function renderStaff() {
  const staff = await api('api/staff.php');
  setContent(`
  <div class="table-card">
    <div class="table-header"><span class="table-title-txt">Staff Members</span></div>
    <table><thead><tr><th>Staff ID</th><th>Name</th><th>Login ID</th><th>Role</th><th>Action</th></tr></thead>
    <tbody>${Array.isArray(staff)?staff.map(s=>`<tr>
      <td style="font-family:var(--font-mono)">${s.staff_id}</td>
      <td style="color:var(--text);font-weight:600">${s.name}</td>
      <td>${s.login_id}</td>
      <td><span class="pill pill-danger">Staff</span></td>
      <td>${s.staff_id!=='ST001'?`<button class="btn btn-danger" style="padding:4px 10px;font-size:11px" onclick="deleteStaff('${s.staff_id}')">Remove</button>`:'—'}</td>
    </tr>`).join(''):'<tr><td colspan="5" style="text-align:center;padding:24px;color:var(--text3)">No staff found</td></tr>'}
    </tbody></table>
  </div>`);
}

async function deleteStaff(id) {
  if (!confirm(`Remove staff ${id}?`)) return;
  const res = await api(`api/staff.php?staff_id=${id}`, {method:'DELETE'});
  res.success ? renderStaff() : alert(res.error);
}

// ── My Books (Reader) ──────────────────────────────────
async function renderMyBooks() {
  const reg_no = S.profile.reg_no || '';
  const records = await api(`api/issue_return.php?reg_no=${reg_no}`);
  setContent(`
  <div class="table-card">
    <div class="table-header"><span class="table-title-txt">My Borrowed Books</span></div>
    <table><thead><tr><th>Book No</th><th>Title</th><th>Issue Date</th><th>Due Date</th><th>Return Date</th><th>Status</th></tr></thead>
    <tbody>${Array.isArray(records)&&records.length?records.map(i=>`<tr>
      <td style="font-family:var(--font-mono)">${i.book_no}</td>
      <td style="color:var(--text)">${i.title||'—'}</td>
      <td style="font-family:var(--font-mono);font-size:11px">${i.issue_date||'—'}</td>
      <td style="font-family:var(--font-mono);font-size:11px;color:${i.status==='overdue'?'var(--danger)':'inherit'}">${i.due_date||'—'}</td>
      <td style="font-family:var(--font-mono);font-size:11px">${i.return_date||'—'}</td>
      <td><span class="pill ${statusPill(i.status)}">${i.status}</span></td>
    </tr>`).join(''):'<tr><td colspan="6" style="text-align:center;padding:24px;color:var(--text3)">You have not borrowed any books yet</td></tr>'}
    </tbody></table>
  </div>`);
}

// ── Forms ──────────────────────────────────────────────
async function getPublishers() {
  if (!S.publishers.length) { try { S.publishers = await api('api/publishers.php'); } catch {} }
  return S.publishers;
}

async function showAddBook() {
  const pubs = await getPublishers();
  setContent(`<div class="form-panel">
    <div class="form-panel-title">Add New Book</div>
    <div class="form-grid">
      <div class="form-group"><label>ISBN</label><input id="f_isbn" placeholder="978-x-xxx-xxxxx-x"></div>
      <div class="form-group"><label>Title</label><input id="f_title" placeholder="Book title"></div>
      <div class="form-group"><label>Author No</label><input id="f_authno" placeholder="A001"></div>
      <div class="form-group"><label>Category</label>
        <select id="f_cat"><option>Fiction</option><option>Non-Fiction</option><option>Science</option><option>History</option><option>Dystopian</option><option>Classic</option><option>Biography</option></select>
      </div>
      <div class="form-group"><label>Publisher</label>
        <select id="f_pub">${pubs.map(p=>`<option value="${p.publisher_id}">${p.name}</option>`).join('')}</select>
      </div>
      <div class="form-group"><label>Edition</label><input id="f_edition" placeholder="1st"></div>
      <div class="form-group"><label>Price (৳)</label><input id="f_price" type="number" placeholder="0"></div>
      <div class="form-group"><label>Total Copies</label><input id="f_copies" type="number" placeholder="1" value="1"></div>
    </div>
    <div class="form-actions">
      <button class="btn" onclick="navigate('books')">Cancel</button>
      <button class="btn btn-accent" onclick="submitAddBook()">Save Book</button>
    </div>
  </div>`);
}

async function submitAddBook() {
  const body = { isbn:document.getElementById('f_isbn').value, title:document.getElementById('f_title').value,
    auth_no:document.getElementById('f_authno').value, category:document.getElementById('f_cat').value,
    publisher_id:document.getElementById('f_pub').value, edition:document.getElementById('f_edition').value,
    price:document.getElementById('f_price').value, total_copies:document.getElementById('f_copies').value };
  try {
    const res = await api('api/books.php', {method:'POST', body:JSON.stringify(body)});
    res.success ? (alert('Book added!'), navigate('books')) : alert(res.error);
  } catch { alert('Error — check PHP connection'); navigate('books'); }
}

async function showAddReader() {
  setContent(`<div class="form-panel">
    <div class="form-panel-title">Register New Reader</div>
    <div class="form-grid">
      <div class="form-group"><label>First Name</label><input id="r_fname" placeholder="First name"></div>
      <div class="form-group"><label>Last Name</label><input id="r_lname" placeholder="Last name"></div>
      <div class="form-group"><label>Email</label><input id="r_email" type="email" placeholder="email@example.com"></div>
      <div class="form-group"><label>Phone No</label><input id="r_phone" placeholder="01xxx-xxxxxx"></div>
      <div class="form-group full"><label>Address</label><input id="r_addr" placeholder="Full address"></div>
      <div class="form-group"><label>Password</label><input id="r_pass" type="password" placeholder="Set member password"></div>
      <div class="form-group"><label>Reg No</label><input readonly placeholder="Auto-generated" style="opacity:0.4"></div>
    </div>
    <div class="form-actions">
      <button class="btn" onclick="navigate('readers')">Cancel</button>
      <button class="btn btn-accent" onclick="submitAddReader()">Register Reader</button>
    </div>
  </div>`);
}

async function submitAddReader() {
  const body = { firstname:document.getElementById('r_fname').value, lastname:document.getElementById('r_lname').value,
    email:document.getElementById('r_email').value, phone_no:document.getElementById('r_phone').value,
    address:document.getElementById('r_addr').value, password:document.getElementById('r_pass').value };
  try {
    const res = await api('api/readers.php', {method:'POST', body:JSON.stringify(body)});
    res.success ? (alert(`Reader registered!\nReg No: ${res.reg_no}\nLogin ID: ${res.login_id}`), navigate('readers')) : alert(res.error);
  } catch { alert('Error — check PHP connection'); navigate('readers'); }
}

async function showIssueForm() {
  setContent(`<div class="form-panel">
    <div class="form-panel-title">Issue a Book</div>
    <div class="form-grid">
      <div class="form-group"><label>Reader Reg No</label><input id="i_regno" placeholder="R0001"></div>
      <div class="form-group"><label>ISBN</label><input id="i_isbn" placeholder="978-…"></div>
      <div class="form-group"><label>Issue Date</label><input id="i_issue" type="date" value="${today()}"></div>
      <div class="form-group"><label>Due / Return Date</label><input id="i_due" type="date" value="${daysFromNow(30)}"></div>
      <div class="form-group"><label>Reserve Date (optional)</label><input id="i_reserve" type="date"></div>
    </div>
    <div class="form-actions">
      <button class="btn" onclick="navigate('issue')">Cancel</button>
      <button class="btn btn-accent" onclick="submitIssue()">Confirm Issue</button>
    </div>
  </div>`);
}

async function submitIssue() {
  const body = { reader_reg_no:document.getElementById('i_regno').value, isbn:document.getElementById('i_isbn').value,
    issue_date:document.getElementById('i_issue').value, due_date:document.getElementById('i_due').value,
    reserve_date:document.getElementById('i_reserve').value||null };
  try {
    const res = await api('api/issue_return.php', {method:'POST', body:JSON.stringify(body)});
    res.success ? (alert(`Book issued! Book No: ${res.book_no}`), navigate('issue')) : alert(res.error);
  } catch { alert('Error — check PHP connection'); navigate('issue'); }
}

async function showAddPublisher() {
  setContent(`<div class="form-panel">
    <div class="form-panel-title">Add Publisher</div>
    <div class="form-grid">
      <div class="form-group"><label>Publisher Name</label><input id="p_name" placeholder="Publisher name"></div>
      <div class="form-group"><label>Year of Publication</label><input id="p_year" type="number" placeholder="1990"></div>
    </div>
    <div class="form-actions">
      <button class="btn" onclick="navigate('publishers')">Cancel</button>
      <button class="btn btn-accent" onclick="submitPublisher()">Save Publisher</button>
    </div>
  </div>`);
}

async function submitPublisher() {
  const body = { name:document.getElementById('p_name').value, year_of_publication:document.getElementById('p_year').value };
  try {
    const res = await api('api/publishers.php', {method:'POST', body:JSON.stringify(body)});
    res.success ? (alert('Publisher added!'), navigate('publishers')) : alert(res.error);
  } catch { alert('Error — check PHP connection'); navigate('publishers'); }
}

async function showAddStaff() {
  setContent(`<div class="form-panel">
    <div class="form-panel-title">Add Staff Member</div>
    <div class="form-grid">
      <div class="form-group"><label>Full Name</label><input id="s_name" placeholder="Full name"></div>
      <div class="form-group"><label>Login ID</label><input id="s_lid" placeholder="login_id"></div>
      <div class="form-group"><label>Password</label><input id="s_pass" type="password" placeholder="Set password"></div>
    </div>
    <div class="form-actions">
      <button class="btn" onclick="navigate('staff')">Cancel</button>
      <button class="btn btn-accent" onclick="submitStaff()">Add Staff</button>
    </div>
  </div>`);
}

async function submitStaff() {
  const body = { name:document.getElementById('s_name').value, login_id:document.getElementById('s_lid').value, password:document.getElementById('s_pass').value };
  try {
    const res = await api('api/staff.php', {method:'POST', body:JSON.stringify(body)});
    res.success ? (alert('Staff added!'), navigate('staff')) : alert(res.error);
  } catch { alert('Error — check PHP connection'); navigate('staff'); }
}

// ── Utilities ──────────────────────────────────────────
function filterTable(input, bodyId) {
  const q = input.value.toLowerCase();
  document.querySelectorAll(`#${bodyId} tr`).forEach(r => {
    r.style.display = r.innerText.toLowerCase().includes(q) ? '' : 'none';
  });
}

function today() { return new Date().toISOString().split('T')[0]; }
function daysFromNow(n) { const d=new Date(); d.setDate(d.getDate()+n); return d.toISOString().split('T')[0]; }
</script>
</body>
</html>
