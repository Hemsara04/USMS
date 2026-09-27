const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const source = fs.readFileSync(__dirname + '/../assets/js/athletic_regestration.js', 'utf8');
const markup = fs.readFileSync(__dirname + '/../views/athletic_regestration.php', 'utf8');
const targets = [...markup.matchAll(/class="nav-item [^\n]*?data-target="([^"]+)"/g)].map(match => match[1]);
assert(targets.includes('calendar-view'));
function element(id) {
    const classes = new Set();
    return { id, classList: { add: name => classes.add(name), remove: name => classes.delete(name), contains: name => classes.has(name) }, getAttribute: () => id };
}
const navItems = targets.map(element);
const views = [...targets, 'edit-profile-view', 'application-details-view', 'edit-application-view', 'submission-view'].map(element);
let currentUrl;
const context = { navItems, views, URL, window: { location: { href: 'http://localhost/USMS/views/athletic_regestration.php?view=calendar-view' }, history: { replaceState: (_, __, url) => currentUrl = url }, scrollTo() {} } };
vm.createContext(context);
vm.runInContext(source.slice(source.indexOf('    function switchView('), source.indexOf('    // Sidebar Navigation Click Handlers')), context);
for (const target of views.map(view => view.id)) {
    context.switchView('calendar-view');
    context.switchView(target);
    assert.equal(navItems.filter(nav => nav.classList.contains('active')).length, 1);
    assert.equal(views.filter(view => view.classList.contains('active')).length, 1);
    assert.equal(views.find(view => view.classList.contains('active')).id, target);
    const parent = { 'edit-profile-view': 'profile-view', 'application-details-view': 'applications-view', 'edit-application-view': 'applications-view', 'submission-view': 'apply-view' }[target] || target;
    assert.equal(navItems.find(nav => nav.classList.contains('active')).id, parent);
    assert.equal(currentUrl.searchParams.get('view'), target.includes('application-') ? 'applications-view' : target);
}
console.log('PASS: calendar navigation, exactly one active section and sidebar item, editor parent and URL synchronization.');
