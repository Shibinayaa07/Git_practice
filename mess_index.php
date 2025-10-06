
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MIC - Mess Management</title>
    <link rel="icon" type="image/png" sizes="32x32" href="image/icons/mkces.png">
    <link rel="stylesheet" href="style.css">
    
    <!-- Bootstrap 5 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <!-- DataTables -->
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    
    <!-- jQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    
    <style>
        :root {
            --sidebar-width: 250px;
            --sidebar-collapsed-width: 70px;
            --topbar-height: 60px;
            --footer-height: 60px;
            --primary-color: #4e73df;
            --secondary-color: #858796;
            --success-color: #1cc88a;
            --dark-bg: #1a1c23;
            --light-bg: #f8f9fc;
            --card-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .content {
            margin-left: var(--sidebar-width);
            padding-top: var(--topbar-height);
            transition: all 0.3s ease;
            min-height: 100vh;
        }
        
        .sidebar.collapsed ~ .content {
            margin-left: var(--sidebar-collapsed-width);
        }
        
        .breadcrumb-area {
            background-image: linear-gradient(to top, #fff1eb 0%, #ace0f9 100%);
            border-radius: 10px;
            box-shadow: var(--card-shadow);
            margin: 20px;
            padding: 15px 20px;
        }
        
        .breadcrumb-item a {
            color: var(--primary-color);
            text-decoration: none;
            transition: var(--transition);
        }
        
        .breadcrumb-item a:hover {
            color: #224abe;
        }
        
        .container-fluid {
            padding: 20px;
        }
        
        .loader-container {
            position: fixed;
            left: var(--sidebar-width);
            right: 0;
            top: var(--topbar-height);
            bottom: var(--footer-height);
            background: rgba(255, 255, 255, 0.95);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            transition: left 0.3s ease;
        }
        
        .sidebar.collapsed ~ .content .loader-container {
            left: var(--sidebar-collapsed-width);
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                width: var(--sidebar-width) !important;
            }
            
            .sidebar.mobile-show {
                transform: translateX(0);
            }
            
            .content {
                margin-left: 0 !important;
            }
            
            .loader-container {
                left: 0;
            }
        }
        
        .loader-container.hide {
            display: none;
        }
        
        .loader {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-radius: 50%;
            border-top: 5px solid var(--primary-color);
            border-right: 5px solid var(--success-color);
            border-bottom: 5px solid var(--primary-color);
            border-left: 5px solid var(--success-color);
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .table-responsive {
            border-radius: 10px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
            margin-top: 20px;
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .table thead th {
            background: var(--primary-color);
            color: white;
            font-weight: 600;
            border: none;
            padding: 15px 10px;
        }
        
        .table tbody td {
            padding: 12px 10px;
            vertical-align: middle;
            border-bottom: 1px solid #e3e6f0;
        }
        
        .table tbody tr:hover {
            background-color: #f8f9fc;
        }
        
        .btn-group-sm .btn {
            margin: 0 2px;
        }
        
        .badge {
            font-size: 0.75em;
            padding: 0.5em 0.75em;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="content">
        <div class="loader-container hide" id="loaderContainer">
            <div class="loader"></div>
        </div>
        
        <!-- Topbar -->
        <?php include 'topbar.php'; ?>
        
        <!-- Breadcrumb -->
        <div class="breadcrumb-area custom-gradient">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="#">Mess Menu</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Management</li>
                </ol>
            </nav>
        </div>
        
        <!-- Content Area -->
        <div class="container-fluid">
            <div class="custom-tabs">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" id="dailymenu-main-tab" href="#dailymenu" role="tab" aria-selected="true">
                            <span style="font-size: 0.9em;"><i class="fas fa-utensils tab-icon"></i> Daily Menu</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" id="specialtoken-main-tab" href="#specialtoken" role="tab" aria-selected="false">
                            <span style="font-size: 0.9em;"><i class="fas fa-mug-hot tab-icon"></i> Special Token</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" id="viewmenu-main-tab" href="#viewmenu" role="tab" aria-selected="false">
                            <span style="font-size: 0.9em;"><i class="fas fa-list tab-icon"></i> View Menu</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" id="tokens-main-tab" href="#tokens" role="tab" aria-selected="false">
                            <span style="font-size: 0.9em;"><i class="fas fa-ticket-alt tab-icon"></i> View Tokens</span>
                        </a>
                    </li>
                </ul>
                
                <div class="tab-content">
                    <!-- Daily Menu Tab -->
                    <div class="tab-pane fade show active" id="dailymenu" role="tabpanel">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <button type="button" class="btn w-100" id="breakfast" data-bs-toggle="modal" data-bs-target="#breakfastModal">
                                    <i class="fas fa-coffee"></i> Breakfast
                                </button>
                            </div>
                            <div class="col-md-3 mb-3">
                                <button type="button" class="btn w-100" id="breakfast"data-bs-toggle="modal" data-bs-target="#lunchModal">
                                    <i class="fas fa-hamburger"></i> Lunch
                                </button>
                            </div>
                            <div class="col-md-3 mb-3">
                                <button type="button" class="btn w-100" id="breakfast"data-bs-toggle="modal" data-bs-target="#snacksModal">
                                    <i class="fas fa-cookie"></i> Snacks
                                </button>
                            </div>
                            <div class="col-md-3 mb-3">
                                <button type="button" class="btn  w-100" id="breakfast"data-bs-toggle="modal" data-bs-target="#dinnerModal">
                                    <i class="fas fa-utensils"></i> Dinner
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Special Token Tab -->
                    <div class="tab-pane fade" id="specialtoken" role="tabpanel">
                        <button type="button" class="btn " id="breakfast"data-bs-toggle="modal" data-bs-target="#specialtokenModal">
                            <i class="fas fa-plus"></i> Enable Special Token
                        </button>
                    </div>
                    
                    <!-- View Menu Tab -->
                    <div class="tab-pane fade" id="viewmenu" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-striped" id="messMenuTable">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Meal Type</th>
                                        <th>Items</th>
                                        <th>Category</th>
                                        <th>Fee</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data will be populated by DataTables -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- View Tokens Tab -->
                    <div class="tab-pane fade" id="tokens" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-striped" id="messTokensTable">
                                <thead>
                                    <tr>
                                        <th>Student Name</th>
                                        <th>Roll Number</th>
                                        <th>Meal Type</th>
                                        <th>Date</th>
                                        <th>Token Type</th>
                                        <th>Special Fee</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data will be populated by DataTables -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Breakfast Modal -->
    <div class="modal fade" id="breakfastModal" tabindex="-1" aria-labelledby="breakfastModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="breakfastModalLabel">Breakfast Menu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="breakfastMenuForm" method="post">
                        <div class="mb-3">
                            <label for="breakfastDate" class="form-label">Date</label>
                            <input type="date" class="form-control" id="breakfastDate" name="date" required>
                        </div>
                        <div class="mb-3">
                            <label for="breakfastItems" class="form-label">Menu Items</label>
                            <textarea class="form-control" id="breakfastItems" name="items" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="breakfastCategory" class="form-label">Category</label>
                            <input type="text" class="form-control" id="breakfastCategory" name="category" placeholder="Regular/Special">
                        </div>
                        <div class="mb-3">
                            <label for="breakfastFee" class="form-label">Fee</label>
                            <input type="number" step="0.01" class="form-control" id="breakfastFee" name="fee" required>
                        </div>
                        <input type="hidden" name="meal_type" value="Breakfast">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" data-save-form="breakfastMenuForm">Save changes</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Lunch Modal -->
    <div class="modal fade" id="lunchModal" tabindex="-1" aria-labelledby="lunchModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="lunchModalLabel">Lunch Menu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="lunchMenuForm" method="post">
                        <div class="mb-3">
                            <label for="lunchDate" class="form-label">Date</label>
                            <input type="date" class="form-control" id="lunchDate" name="date" required>
                        </div>
                        <div class="mb-3">
                            <label for="lunchItems" class="form-label">Menu Items</label>
                            <textarea class="form-control" id="lunchItems" name="items" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="lunchCategory" class="form-label">Category</label>
                            <input type="text" class="form-control" id="lunchCategory" name="category" placeholder="Regular/Special">
                        </div>
                        <div class="mb-3">
                            <label for="lunchFee" class="form-label">Fee</label>
                            <input type="number" step="0.01" class="form-control" id="lunchFee" name="fee" required>
                        </div>
                        <input type="hidden" name="meal_type" value="Lunch">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" data-save-form="lunchMenuForm">Save changes</button>
                </div>
            </div>
        </div>
    </div>
     <!-- Snacks model -->
        <div class="modal fade" id="snacksModal" tabindex="-1" aria-labelledby="snacksModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="snacksModalLabel">Snacks Menu</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                         <form id="snacksMenuForm" method="post"></form>
                            <div class="mb-3">
                                <label for="snacksDate" class="form-label">Date</label>
                                <input type="date" class="form-control" id="snacksDate" name="date" required>
                            </div>
                            <div class="mb-3">
                                <label for="snacksItems" class="form-label">Menu Items</label>
                                <textarea class="form-control" id="snacksItems" name="items" rows="3" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="snacksCategory" class="form-label">Category</label>
                                <input type="text" class="form-control" id="snacksCategory" name="category" placeholder="Regular/Special">
                            </div>
                            <div class="mb-3">
                                <label for="snacksFee" class="form-label">Fee (₹)</label>
                                <input type="number" step="0.01" class="form-control" id="snacksFee" name="fee" required>
                            </div>
                            <input type="hidden" name="meal_type" value="Snacks">
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary">Save changes</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Dinner Modal -->
        <div class="modal fade" id="dinnerModal" tabindex="-1" aria-labelledby="dinnerModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="dinnerModalLabel">Dinner Menu</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                         <form id="dinnerMenuForm" method="post"></form>
                            <div class="mb-3">
                                <label for="dinnerDate" class="form-label">Date</label>
                                <input type="date" class="form-control" id="dinnerDate" name="date" required>
                            </div>
                            <div class="mb-3">
                                <label for="dinnerItems" class="form-label">Menu Items</label>
                                <textarea class="form-control" id="dinnerItems" name="items" rows="3" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="dinnerCategory" class="form-label">Category</label>
                                <input type="text" class="form-control" id="dinnerCategory" name="category" placeholder="Regular/Special">
                            </div>
                            <div class="mb-3">
                                <label for="dinnerFee" class="form-label">Fee (₹)</label>
                                <input type="number" step="0.01" class="form-control" id="dinnerFee" name="fee" required>
                            </div>
                            <input type="hidden" name="meal_type" value="Dinner">
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary">Save changes</button>
                    </div>
                </div>
            </div>
        </div>
        <!--Special Token-->
        <div class="modal fade" id="specialtokenModal" tabindex="-1" aria-labelledby="specialtokenModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="specialtokenModalLabel">Special Token</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="tokenenableForm" method="post"></form>
                        <div class="mb-3">
                            <label for="tokenfromDate" class="form-label">From Date</label>
                            <input type="date" class="form-control" id="tokenfromDate" name="date" required>
                        </div>
                        <div class="mb-3">
                            <label for="tokenfromTime" class="form-label">From Time</label>
                            <input type="time" class="form-control" id="tokenfromTime" name="time" required>
                        </div>
                        <div class="mb-3">
                            <label for="tokentoDate" class="form-label">To Date</label>
                            <input type="date" class="form-control" id="tokentoDate" name="date" required>
                        </div>
                        <div class="mb-3">
                            <label for="tokentoTime" class="form-label">To Time</label>
                            <input type="time" class="form-control" id="tokentoTime" name="time" required>
                        </div>
                        <div class="mb-3">
                            <label for="breakfastItems" class="form-label">Menu Items</label>
                            <textarea class="form-control" id="breakfastItems" name="items" rows="3" required></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="specialtokenFee" class="form-label">Fee (₹)</label>
                            <input type="number" step="0.01" class="form-control" id="specialtokenFee" name="fee" required>
                        </div>
                        
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary">Enable token </button>
                    </div>
                </div>
            </div>
        </div>

    <!-- Edit Menu Modal -->
    <div class="modal fade" id="editMenuModal" tabindex="-1" aria-labelledby="editMenuModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editMenuModalLabel">Edit Menu Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editMenuForm">
                        <input type="hidden" id="editMenuId" name="menu_id">
                        <div class="mb-3">
                            <label for="editMenuDate" class="form-label">Date</label>
                            <input type="date" class="form-control" id="editMenuDate" name="date" required>
                        </div>
                        <div class="mb-3">
                            <label for="editMenuMealType" class="form-label">Meal Type</label>
                            <select class="form-control" id="editMenuMealType" name="meal_type" required>
                                <option value="Breakfast">Breakfast</option>
                                <option value="Lunch">Lunch</option>
                                <option value="Snacks">Snacks</option>
                                <option value="Dinner">Dinner</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="editMenuItems" class="form-label">Menu Items</label>
                            <textarea class="form-control" id="editMenuItems" name="items" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="editMenuCategory" class="form-label">Category</label>
                            <input type="text" class="form-control" id="editMenuCategory" name="category" placeholder="Regular/Special">
                        </div>
                        <div class="mb-3">
                            <label for="editMenuFee" class="form-label">Fee</label>
                            <input type="number" step="0.01" class="form-control" id="editMenuFee" name="fee" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="updateMenu()">Update Menu</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Token Details Modal -->
    <div class="modal fade" id="tokenDetailsModal" tabindex="-1" aria-labelledby="tokenDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tokenDetailsModalLabel">Token Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="tokenDetails">
                    <!-- Token details will be populated here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'footer.php'; ?>

    <!-- Custom JavaScript -->
    <script src="app.js"></script>
    
    <script>
        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            // Set today's date as default for all date inputs
            const today = new Date().toISOString().split('T')[0];
            document.querySelectorAll('input[type="date"]').forEach(input => {
                if (!input.value) {
                    input.value = today;
                }
            });
        });
        
        // Update menu function for edit modal
        async function updateMenu() {
            const form = document.getElementById('editMenuForm');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());
            const menuId = data.menu_id;
            
            try {
                await MessAPI.api.updateMessMenu(menuId, data);
                MessAPI.formHandler.handleSuccess('Menu updated successfully');
                
                const editModal = bootstrap.Modal.getInstance(document.getElementById('editMenuModal'));
                editModal.hide();
                
                MessAPI.dataTableManager.refreshTable('messMenu');
            } catch (error) {
                MessAPI.formHandler.handleError(error.message);
            }
        }
        
        // Sidebar and loader logic from original index.php
        document.addEventListener('DOMContentLoaded', function() {
            const loaderContainer = document.getElementById('loaderContainer');
            let loadingTimeout;
            
            function hideLoader() {
                loaderContainer.classList.add('hide');
            }
            
            function showError() {
                console.error('Page load took too long or encountered an error');
            }
            
            loadingTimeout = setTimeout(showError, 10000);
            
            window.onload = function() {
                clearTimeout(loadingTimeout);
                setTimeout(hideLoader, 500);
            };
            
            window.onerror = function(msg, url, lineNo, columnNo, error) {
                clearTimeout(loadingTimeout);
                showError();
                return false;
            };
        });
    </script>
</body>
</html>