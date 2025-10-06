// Main JavaScript functionality for Mess Management System

class MessManagementAPI {
    constructor() {
        this.baseURL = window.location.origin + '/api';
        this.token = localStorage.getItem('auth_token');
    }

    // Generic API call method
    async request(endpoint, method = 'GET', data = null) {
        try {
            const config = {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                }
            };

            if (this.token) {
                config.headers['Authorization'] = `Bearer ${this.token}`;
            }

            if (data && method !== 'GET') {
                config.body = JSON.stringify(data);
            }

            const response = await fetch(`${this.baseURL}/${endpoint}`, config);
            const result = await response.json();

            if (!result.success) {
                throw new Error(result.message || 'API request failed');
            }

            return result;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    }

    // Mess Menu API methods
    async getMessMenu(filters = {}) {
        const params = new URLSearchParams(filters);
        const endpoint = `mess-menu${params.toString() ? '?' + params.toString() : ''}`;
        return await this.request(endpoint);
    }

    async createMessMenu(menuData) {
        return await this.request('mess-menu', 'POST', menuData);
    }

    async updateMessMenu(menuId, menuData) {
        return await this.request(`mess-menu/${menuId}`, 'PUT', menuData);
    }

    async deleteMessMenu(menuId) {
        return await this.request(`mess-menu/${menuId}`, 'DELETE');
    }

    // Mess Token API methods
    async getMessTokens(filters = {}) {
        const params = new URLSearchParams(filters);
        const endpoint = `mess-tokens${params.toString() ? '?' + params.toString() : ''}`;
        return await this.request(endpoint);
    }

    async createMessToken(tokenData) {
        return await this.request('mess-tokens', 'POST', tokenData);
    }

    // Authentication methods
    async login(username, password) {
        return await this.request('auth', 'POST', {
            action: 'login',
            username: username,
            password: password
        });
    }

    async logout() {
        return await this.request('auth', 'POST', { action: 'logout' });
    }
}

// Initialize API instance
const api = new MessManagementAPI();

// Form handling utilities
class FormHandler {
    constructor() {
        this.setupEventListeners();
    }

    setupEventListeners() {
        // Handle all form submissions
        document.addEventListener('submit', (e) => {
            if (e.target.matches('[data-api-form]')) {
                e.preventDefault();
                this.handleFormSubmit(e.target);
            }
        });

        // Handle modal form submissions
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-save-form]')) {
                e.preventDefault();
                const formId = e.target.getAttribute('data-save-form');
                const form = document.getElementById(formId);
                if (form) {
                    this.handleFormSubmit(form);
                }
            }
        });
    }

    async handleFormSubmit(form) {
        try {
            this.showLoading(true);
            
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());
            
            const endpoint = form.getAttribute('data-endpoint');
            const method = form.getAttribute('data-method') || 'POST';
            
            const result = await api.request(endpoint, method, data);
            
            this.handleSuccess(result.message || 'Operation completed successfully');
            
            // Close modal if form is in a modal
            const modal = form.closest('.modal');
            if (modal) {
                const bsModal = bootstrap.Modal.getInstance(modal);
                if (bsModal) {
                    bsModal.hide();
                }
            }
            
            // Reset form
            form.reset();
            
            // Refresh data if needed
            if (form.hasAttribute('data-refresh-table')) {
                const tableId = form.getAttribute('data-refresh-table');
                this.refreshTable(tableId);
            }
            
        } catch (error) {
            this.handleError(error.message);
        } finally {
            this.showLoading(false);
        }
    }

    showLoading(show) {
        const loader = document.getElementById('loaderContainer');
        if (loader) {
            if (show) {
                loader.classList.remove('hide');
            } else {
                loader.classList.add('hide');
            }
        }
    }

    handleSuccess(message) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: message,
                timer: 3000,
                showConfirmButton: false
            });
        } else {
            alert(message);
        }
    }

    handleError(message) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: message
            });
        } else {
            alert('Error: ' + message);
        }
    }

    refreshTable(tableId) {
        const table = document.getElementById(tableId);
        if (table && $.fn.DataTable && $.fn.DataTable.isDataTable(table)) {
            $(table).DataTable().ajax.reload();
        }
    }
}

// Data Table utilities
class DataTableManager {
    constructor() {
        this.tables = {};
        this.initializeTables();
    }

    initializeTables() {
        // Initialize DataTables when DOM is ready
        document.addEventListener('DOMContentLoaded', () => {
            this.setupDataTables();
        });
    }

    setupDataTables() {
        // Setup mess menu table
        const messMenuTable = document.getElementById('messMenuTable');
        if (messMenuTable) {
            this.tables.messMenu = $(messMenuTable).DataTable({
                ajax: {
                    url: api.baseURL + '/mess-menu',
                    dataSrc: 'data'
                },
                columns: [
                    { data: 'date' },
                    { data: 'meal_type' },
                    { data: 'items' },
                    { data: 'category' },
                    { data: 'fee', render: (data) => `₹${parseFloat(data).toFixed(2)}` },
                    {
                        data: null,
                        render: (data, type, row) => {
                            return `
                                <button class="btn btn-sm btn-primary" onclick="editMessMenu(${row.menu_id})">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteMessMenu(${row.menu_id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            `;
                        }
                    }
                ],
                responsive: true,
                pageLength: 10,
                order: [[0, 'desc']]
            });
        }

        // Setup mess tokens table
        const messTokensTable = document.getElementById('messTokensTable');
        if (messTokensTable) {
            this.tables.messTokens = $(messTokensTable).DataTable({
                ajax: {
                    url: api.baseURL + '/mess-tokens',
                    dataSrc: 'data'
                },
                columns: [
                    { data: 'student_name' },
                    { data: 'roll_number' },
                    { data: 'meal_type' },
                    { data: 'menu_date' },
                    { data: 'token_type' },
                    { data: 'special_fee', render: (data) => data ? `₹${parseFloat(data).toFixed(2)}` : '-' },
                    {
                        data: null,
                        render: (data, type, row) => {
                            return `
                                <button class="btn btn-sm btn-info" onclick="viewToken(${row.token_id})">
                                    <i class="fas fa-eye"></i>
                                </button>
                            `;
                        }
                    }
                ],
                responsive: true,
                pageLength: 10,
                order: [[3, 'desc']]
            });
        }
    }

    refreshTable(tableName) {
        if (this.tables[tableName]) {
            this.tables[tableName].ajax.reload();
        }
    }
}

// Menu management functions
async function editMessMenu(menuId) {
    try {
        const result = await api.request(`mess-menu/${menuId}`);
        const menu = result.data;

        // Populate form with existing data
        document.getElementById('editMenuId').value = menu.menu_id;
        document.getElementById('editMenuDate').value = menu.date;
        document.getElementById('editMenuMealType').value = menu.meal_type;
        document.getElementById('editMenuItems').value = menu.items;
        document.getElementById('editMenuCategory').value = menu.category || '';
        document.getElementById('editMenuFee').value = menu.fee;

        // Show edit modal
        const editModal = new bootstrap.Modal(document.getElementById('editMenuModal'));
        editModal.show();

    } catch (error) {
        formHandler.handleError(error.message);
    }
}

async function deleteMessMenu(menuId) {
    if (typeof Swal !== 'undefined') {
        const result = await Swal.fire({
            title: 'Are you sure?',
            text: 'This action cannot be undone!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        });

        if (result.isConfirmed) {
            try {
                await api.deleteMessMenu(menuId);
                formHandler.handleSuccess('Menu item deleted successfully');
                dataTableManager.refreshTable('messMenu');
            } catch (error) {
                formHandler.handleError(error.message);
            }
        }
    } else {
        if (confirm('Are you sure you want to delete this menu item?')) {
            try {
                await api.deleteMessMenu(menuId);
                alert('Menu item deleted successfully');
                location.reload();
            } catch (error) {
                alert('Error: ' + error.message);
            }
        }
    }
}

async function viewToken(tokenId) {
    try {
        const result = await api.request(`mess-tokens/${tokenId}`);
        const token = result.data;

        // Populate token details modal
        const tokenDetails = document.getElementById('tokenDetails');
        if (tokenDetails) {
            tokenDetails.innerHTML = `
                <p><strong>Student:</strong> ${token.student_name} (${token.roll_number})</p>
                <p><strong>Meal:</strong> ${token.meal_type}</p>
                <p><strong>Date:</strong> ${token.menu_date}</p>
                <p><strong>Items:</strong> ${token.items}</p>
                <p><strong>Type:</strong> ${token.token_type}</p>
                ${token.special_fee ? `<p><strong>Special Fee:</strong> ₹${parseFloat(token.special_fee).toFixed(2)}</p>` : ''}
                <p><strong>Created:</strong> ${new Date(token.created_at).toLocaleString()}</p>
            `;
        }

        // Show token details modal
        const tokenModal = new bootstrap.Modal(document.getElementById('tokenDetailsModal'));
        tokenModal.show();

    } catch (error) {
        formHandler.handleError(error.message);
    }
}

// Initialize managers
const formHandler = new FormHandler();
const dataTableManager = new DataTableManager();

// Enhanced form handling for mess menu modals
document.addEventListener('DOMContentLoaded', function() {
    // Breakfast form handler
    const breakfastForm = document.getElementById('breakfastMenuForm');
    if (breakfastForm) {
        breakfastForm.setAttribute('data-api-form', 'true');
        breakfastForm.setAttribute('data-endpoint', 'mess-menu');
        breakfastForm.setAttribute('data-method', 'POST');
        breakfastForm.setAttribute('data-refresh-table', 'messMenu');
    }

    // Lunch form handler
    const lunchForm = document.getElementById('lunchMenuForm');
    if (lunchForm) {
        lunchForm.setAttribute('data-api-form', 'true');
        lunchForm.setAttribute('data-endpoint', 'mess-menu');
        lunchForm.setAttribute('data-method', 'POST');
        lunchForm.setAttribute('data-refresh-table', 'messMenu');
    }

    // Snacks form handler
    const snacksForm = document.getElementById('snacksMenuForm');
    if (snacksForm) {
        snacksForm.setAttribute('data-api-form', 'true');
        snacksForm.setAttribute('data-endpoint', 'mess-menu');
        snacksForm.setAttribute('data-method', 'POST');
        snacksForm.setAttribute('data-refresh-table', 'messMenu');
    }

    // Dinner form handler
    const dinnerForm = document.getElementById('dinnerMenuForm');
    if (dinnerForm) {
        dinnerForm.setAttribute('data-api-form', 'true');
        dinnerForm.setAttribute('data-endpoint', 'mess-menu');
        dinnerForm.setAttribute('data-method', 'POST');
        dinnerForm.setAttribute('data-refresh-table', 'messMenu');
    }

    // Update save buttons to work with forms
    document.querySelectorAll('.modal-footer button[type="button"]:not([data-bs-dismiss])').forEach(button => {
        if (button.textContent.includes('Save') || button.textContent.includes('changes')) {
            const modal = button.closest('.modal');
            if (modal) {
                const form = modal.querySelector('form');
                if (form) {
                    button.setAttribute('data-save-form', form.id);
                }
            }
        }
    });
});

// Export for global use
window.MessAPI = {
    api,
    formHandler,
    dataTableManager,
    editMessMenu,
    deleteMessMenu,
    viewToken
};