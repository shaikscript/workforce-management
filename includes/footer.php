    </main>

    <!-- Shared Footer Content -->
    <footer class="footer mt-auto py-4 bg-body-tertiary border-top">
        <div class="container text-center">
            <span class="text-muted">&copy; <?php echo date('Y'); ?> <?php echo COMPANY_NAME; ?>. All rights reserved.</span>
            <br>
            <small class="text-muted"><?php echo COMPANY_TAGLINE; ?></small>
        </div>
    </footer>

    <!-- Bootstrap 5.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- SheetJS (Excel/CSV parsing) -->
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    
    <!-- html2pdf.js / jsPDF for PDF export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="assets/js/app.js"></script>
    
    <!-- Lucide Icons initialization -->
    <script>
        lucide.createIcons();
    </script>
    
    <?php
    // Additional footer content can be added here
    ?>
</body>
</html>
