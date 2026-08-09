<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipment Dashboard - USMS</title>
    <!-- Link to custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

    <!-- Navbar Section -->
    <nav class="navbar">
        <div class="navbar-brand">
            <!-- Mock Logo & Title -->
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            UCSC Sports
            <span style="color: rgba(255,255,255,0.5); font-weight: 400; margin-left: 1rem; border-left: 1px solid rgba(255,255,255,0.2); padding-left: 1rem;">
                Equipment Request & Inventory Dashboard
            </span>
        </div>
        
        <!-- User Profile -->
        <div class="navbar-menu">
            <div class="user-profile">
                <!-- Fallback user avatar -->
                <img src="https://ui-avatars.com/api/?name=Sarah+Jones&background=random" alt="User" class="user-avatar">
                <span>Coach Sarah Jones</span>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <div class="container">
        
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">Equipment Request & Dashboard</h1>
            <p class="page-subtitle">Realistic, modern sports administration system, generous spacing and clean typography.</p>
        </div>

        <!-- 
            2-Column Grid Layout
            Left: Inventory Table (Takes up more space)
            Right: New Equipment Request Form (Takes up less space)
        -->
        <div class="grid-layout grid-2-cols">
            
            <!-- LEFT COLUMN: Inventory Table Card -->
            <div class="card">
                
                <!-- Header with Title, Search, and Filter -->
                <div class="card-header" style="gap: 1rem;">
                    <h2 class="card-title" style="flex: 1;">Current Sports Equipment Inventory</h2>
                    
                    <!-- Search Input -->
                    <div class="search-bar" style="width: auto; min-width: 200px;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                        <input type="text" class="form-control" placeholder="Search">
                    </div>

                    <!-- Filter Button -->
                    <button class="btn btn-outline btn-sm">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 0.25rem;"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                        Filter
                    </button>
                </div>

                <!-- Table Content -->
                <div class="table-responsive">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>Item Name</th>
                                <th>Total Stock</th>
                                <th>Available Stock</th>
                                <th>Status Badge</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Row 1 -->
                            <!-- In PHP, use foreach($inventory as $item) -->
                            <tr>
                                <td>
                                    <div class="user-cell">
                                        <!-- Replace with actual equipment icons/images -->
                                        ⚽ Footballs
                                    </div>
                                </td>
                                <td>150</td>
                                <td>12</td>
                                <td>
                                    <span class="badge badge-danger">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                                        Low Stock
                                    </span>
                                </td>
                            </tr>
                            
                            <!-- Row 2 -->
                            <tr>
                                <td>
                                    <div class="user-cell">
                                        🏏 Cricket Balls
                                    </div>
                                </td>
                                <td>60</td>
                                <td>45</td>
                                <td>
                                    <span class="badge badge-success">In Stock</span>
                                </td>
                            </tr>

                            <!-- Row 3 -->
                            <tr>
                                <td>
                                    <div class="user-cell">
                                        🎾 Tennis Rackets
                                    </div>
                                </td>
                                <td>30</td>
                                <td>5</td>
                                <td>
                                    <span class="badge badge-success">In Stock</span>
                                </td>
                            </tr>

                            <!-- Row 4 -->
                            <tr>
                                <td>
                                    <div class="user-cell">
                                        🏓 Padel Kits
                                    </div>
                                </td>
                                <td>30</td>
                                <td>5</td>
                                <td>
                                    <span class="badge badge-danger">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                                        Low Stock
                                    </span>
                                </td>
                            </tr>

                            <!-- Row 5 -->
                            <tr>
                                <td>
                                    <div class="user-cell">
                                        🎒 Kits Bags
                                    </div>
                                </td>
                                <td>80</td>
                                <td>72</td>
                                <td>
                                    <span class="badge badge-success">In Stock</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- RIGHT COLUMN: New Request Form Card -->
            <div>
                <div class="card">
                    <!-- Dark themed header for contrast as shown in design -->
                    <div class="card-header card-header-dark">
                        <h2 class="card-title">New Equipment Request Form</h2>
                    </div>
                    
                    <div class="card-body">
                        <!-- Form targeting a controller endpoint e.g., /equipment/request -->
                        <form action="#" method="POST">
                            
                            <!-- Equipment Selection -->
                            <div class="form-group">
                                <label class="form-label">Equipment Item</label>
                                <select name="equipment_id" class="form-control">
                                    <option value="football">Footballs</option>
                                    <option value="cricket_ball">Cricket Balls</option>
                                    <option value="tennis_racket">Tennis Rackets</option>
                                    <option value="padel_kit">Padel Kits</option>
                                    <option value="kit_bag">Kits Bag</option>
                                </select>
                            </div>

                            <!-- Target Match Input -->
                            <div class="form-group">
                                <label class="form-label">Target Match</label>
                                <input type="text" name="target_match" class="form-control" placeholder="e.g. Varsity League Finals">
                            </div>

                            <!-- Submit Button -->
                            <div style="margin-top: 2rem;">
                                <button type="submit" class="btn btn-success btn-block">Submit Request</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div> <!-- End grid-layout -->

    </div> <!-- End container -->

    <footer class="app-footer">
        Minimal footer &copy; <?= date('Y') ?> UCSC Sports
    </footer>

</body>
</html>
