</main>
    
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><?php echo SITE_NAME; ?></h5>
                    <p><?php echo SITE_DESCRIPTION; ?></p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
                    <?php if (!isset($isAdmin) || !$isAdmin): ?>
                        <p><a href="admin/login.php" class="text-white"><i class="fas fa-user-shield me-1"></i>Admin Login</a></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="<?php echo isset($isAdmin) && $isAdmin ? '../' : ''; ?>assets/js/main.js"></script>
    
    <?php if (isset($extraScripts)) echo $extraScripts; ?>
</body>
</html>