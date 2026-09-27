const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const source = fs.readFileSync(__dirname + '/../assets/js/athletic_regestration.js', 'utf8');
const nodes = {};
for (const id of ['application-rejection', 'application-rejection-reason', 'application-statement', 'delete-detail-application', 'edit-detail-application']) {
    nodes[id] = { style: {}, dataset: {}, textContent: '' };
}
const badge = {};
const rows = Array.from({length: 4}, () => ({}));
nodes['application-details-view'] = {
    querySelector: selector => selector.includes('status-badge') ? badge : {},
    querySelectorAll: () => rows,
};
let data;
const button = { dataset: { rejectionReason: '' }, getAttribute: name => data[name] };
const context = {
    document: { querySelectorAll: () => [button], getElementById: id => nodes[id] },
    switchView: () => {},
};
vm.createContext(context);
vm.runInContext(source.slice(source.indexOf('    function bindViewDetailsButtons()'), source.indexOf('    bindViewDetailsButtons();', source.indexOf('    function bindViewDetailsButtons()'))) + '\nbindViewDetailsButtons();', context);
for (const status of ['Rejected', 'Pending', 'Approved', 'Shortlisted', 'Rejected']) {
    data = { 'data-status': status, 'data-appid': '42', 'data-date': '2025-01-02', 'data-statement': 'My statement', 'data-sport': 'Football', 'data-season': '2025' };
    button.dataset.rejectionReason = 'Actual saved rejection reason';
    button.onclick();
    assert.equal(nodes['application-rejection'].style.display, status === 'Rejected' ? '' : 'none');
    assert.equal(nodes['application-rejection-reason'].textContent, status === 'Rejected' ? 'Actual saved rejection reason' : '');
    assert.equal(badge.textContent, status);
    assert.equal(nodes['delete-detail-application'].dataset.appid, '42');
    assert.equal(nodes['edit-detail-application'].dataset.appid, '42');
    assert.equal(nodes['application-statement'].textContent, 'My statement');
    assert.equal(rows[3].textContent, 'APP-2025-0042');
}
button.dataset.rejectionReason = ' ';
button.onclick();
assert.equal(nodes['application-rejection-reason'].textContent, 'No rejection reason was provided.');
console.log('PASS: status switching clears rejection text, saved reason and fallback, statement, delete target and reference year.');
