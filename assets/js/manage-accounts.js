const editor = document.getElementById('accountEditor');
const form = document.getElementById('managedAccountForm');
const rows = document.getElementById('accountRows');
const search = document.getElementById('accountSearch');
const status = document.getElementById('directoryStatus');
const empty = document.getElementById('emptyAccounts');
const title = document.getElementById('accountEditorTitle');
const addTitle = title.textContent;
const fields = document.getElementById('accountEditFields');
const password = form.elements.password;
const passwordHint = document.getElementById('managedPasswordHint');
const deleteMessage = document.getElementById('accountDeleteMessage');
const errorBox = document.getElementById('accountFormError');
const submit = document.getElementById('submitManagedAccount');
const noticeKey = 'gis-account-directory-notice';
let action;

const notice = sessionStorage.getItem(noticeKey);
if (notice) {
    sessionStorage.removeItem(noticeKey);
    status.textContent = notice;
}

search.addEventListener('input', () => {
    const term = search.value.trim().toLowerCase();
    let visible = 0;
    for (const row of rows.rows) {
        row.hidden = ![...row.querySelectorAll('[data-field]')].some(field =>
            field.textContent.toLowerCase().includes(term)
        );
        if (!row.hidden) visible++;
    }
    empty.hidden = visible > 0;
    status.textContent = visible + (visible === 1 ? ' account' : ' accounts');
});

function openEditor(mode, row) {
    action = mode;
    form.reset();
    errorBox.hidden = true;
    const deleting = mode === 'delete';
    const creating = mode === 'create';
    fields.hidden = deleting;
    deleteMessage.hidden = !deleting;
    for (const input of fields.querySelectorAll('input')) input.disabled = deleting;
    password.required = creating;
    passwordHint.textContent = creating ? 'Use at least 8 characters.' : 'Leave blank to keep the current password.';
    submit.classList.toggle('danger', deleting);
    submit.classList.toggle('primary', !deleting);
    title.textContent = creating ? addTitle : deleting ? 'Delete store admin' : 'Edit store admin';
    submit.textContent = creating ? 'Create account' : deleting ? 'Delete account' : 'Save changes';

    if (row) {
        form.elements.id.value = row.dataset.id;
        for (const cell of row.querySelectorAll('[data-field]')) {
            form.elements[cell.dataset.field].value = cell.textContent;
        }
        if (deleting) {
            deleteMessage.textContent = 'Permanently delete the store admin account for ' + form.elements.username.value + '?';
        }
    }

    editor.showModal();
    (deleting ? editor.querySelector('.account-editor-actions [data-dismiss]') : form.elements.username).focus();
}

document.getElementById('addAccount').addEventListener('click', () => openEditor('create'));
rows.addEventListener('click', event => {
    const button = event.target.closest('button');
    if (button) openEditor(button.hasAttribute('data-delete') ? 'delete' : 'update', button.closest('tr'));
});
editor.querySelectorAll('[data-dismiss]').forEach(button => {
    button.addEventListener('click', () => editor.close());
});
editor.addEventListener('cancel', event => {
    if (submit.disabled) event.preventDefault();
});

form.addEventListener('submit', async event => {
    event.preventDefault();
    submit.disabled = true;
    for (const button of editor.querySelectorAll('[data-dismiss]')) button.disabled = true;
    errorBox.hidden = true;
    const body = new FormData(form);
    body.set('action', action);
    try {
        const response = await fetch('../controllers/admin/add_admin_account.php', { method: 'POST', body });
        const result = await response.json();
        if (!response.ok || !result.success) throw new Error(result.message || 'Could not save the account change.');
        sessionStorage.setItem(noticeKey, result.message);
        window.location.reload();
    } catch (error) {
        errorBox.textContent = error.message;
        errorBox.hidden = false;
        submit.disabled = false;
        for (const button of editor.querySelectorAll('[data-dismiss]')) button.disabled = false;
    }
});
