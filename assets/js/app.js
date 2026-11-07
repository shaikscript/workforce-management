/**
 * AI SQUARE HUB - Core JavaScript Functions
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize theme switcher
    initThemeSwitcher();
    
    // Initialize toasts
    initToasts();
    
    // Initialize CSV/XLSX parser
    initSheetJS();
    
    // Initialize PDF export
    initPdfExport();
    
    // Initialize chart helpers
    initCharts();
    
    // Initialize tab switch warning (for candidate exams)
    initTabSwitchWarning();
});

// Theme Switcher
function initThemeSwitcher() {
    const themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', function() {
            const currentTheme = document.documentElement.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            document.documentElement.setAttribute('data-bs-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            
            // Update cookie for PHP
            document.cookie = `theme=${newTheme}; path=/; max-age=31536000`;
        });
    }
}

// Toast Notifications
function initToasts() {
    window.showToast = function(message, type = 'success') {
        const toastContainer = document.getElementById('toast-container') || createToastContainer();
        const toastId = 'toast-' + Date.now();
        
        const toastHtml = `
            <div id="${toastId}" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body bg-${type}">
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;
        
        toastContainer.insertAdjacentHTML('beforeend', toastHtml);
        const toastElement = document.getElementById(toastId);
        const toast = new bootstrap.Toast(toastElement);
        toast.show();
        
        // Auto-remove after animation
        setTimeout(() => {
            toastElement.remove();
        }, 3500);
    };
}

function createToastContainer() {
    const container = document.createElement('div');
    container.className = 'position-fixed bottom-0 end-0 p-3';
    container.id = 'toast-container';
    document.body.appendChild(container);
    return container;
}

// SheetJS CSV/XLSX Parser
function initSheetJS() {
    window.parseSpreadsheet = function(file, callback) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            const data = new Uint8Array(e.target.result);
            const workbook = XLSX.read(data, {type: 'array'});
            const firstSheet = workbook.Sheets[workbook.SheetNames[0]];
            const jsonData = XLSX.utils.sheet_to_json(firstSheet);
            
            callback(jsonData);
        };
        
        reader.readAsArrayBuffer(file);
    };
    
    window.exportToCsv = function(data, filename) {
        const ws = XLSX.utils.json_to_sheet(data);
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, "Sheet1");
        XLSX.writeFile(wb, filename + ".csv");
    };
}

// PDF Export (html2pdf.js)
function initPdfExport() {
    window.exportToPdf = function(elementId, filename) {
        const element = document.getElementById(elementId);
        if (!element) {
            showToast('Element not found for PDF export', 'danger');
            return;
        }
        
        const opt = {
            margin:       10,
            filename:     filename + '.pdf',
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { scale: 2 },
            jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
        };
        
        html2pdf().set(opt).from(element).save();
    };
}

// Chart Helpers
function initCharts() {
    window.createBarChart = function(canvasId, labels, data, title) {
        const ctx = document.getElementById(canvasId).getContext('2d');
        return new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: title,
                    data: data,
                    backgroundColor: '#2563EB',
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                }
            }
        });
    };
    
    window.createLineChart = function(canvasId, labels, data, title) {
        const ctx = document.getElementById(canvasId).getContext('2d');
        return new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: title,
                    data: data,
                    borderColor: '#2563EB',
                    tension: 0.3,
                    fill: true,
                    backgroundColor: 'rgba(37, 99, 235, 0.1)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    };
}

// Tab Switch Warning (Candidate Exam Security)
function initTabSwitchWarning() {
    if (document.getElementById('exam-interface')) {
        let tabSwitchCount = 0;
        const warningModal = document.getElementById('tab-warning-modal');
        const warningText = document.getElementById('tab-warning-text');
        
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                tabSwitchCount++;
                warningText.textContent = `Warning: Tab switch detected and logged (#${tabSwitchCount})`;
                
                if (warningModal) {
                    const modal = new bootstrap.Modal(warningModal);
                    modal.show();
                }
                
                // Log to backend (AJAX)
                logTabSwitch(tabSwitchCount);
            }
        });
        
        window.addEventListener('blur', function() {
            if (document.getElementById('exam-interface')) {
                tabSwitchCount++;
                // Similar logging for window blur
                logTabSwitch(tabSwitchCount);
            }
        });
    }
}

function logTabSwitch(count) {
    // AJAX call to backend endpoint
    fetch('candidate/ajax/log_security.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            '_csrf': document.querySelector('input[name="_csrf"]')?.value || '',
            'action': 'log_tab_switch',
            'count': count,
            'timestamp': new Date().toISOString()
        })
    })
    .catch(error => console.error('Security log failed:', error));
}

// Attendance Check-in/out
function initAttendance() {
    window.checkIn = function() {
        showToast('Checking in...', 'info');
        // Implement AJAX call to employee/ajax/attendance.php
    };
    
    window.checkOut = function() {
        showToast('Checking out...', 'info');
        // Implement AJAX call to employee/ajax/attendance.php
    };
}

// CSRF Token Helper
function getCsrfToken() {
    const tokenInput = document.querySelector('input[name="_csrf"]');
    return tokenInput ? tokenInput.value : '';
}

// Form Submission with CSRF
function submitFormWithCsrf(formId, successCallback, errorCallback) {
    const form = document.getElementById(formId);
    if (!form) return;
    
    const formData = new FormData(form);
    formData.append('_csrf', getCsrfToken());
    
    fetch(form.action, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message || 'Success', 'success');
            if (successCallback) successCallback(data);
        } else {
            showToast(data.message || 'Error', 'danger');
            if (errorCallback) errorCallback(data);
        }
    })
    .catch(error => {
        showToast('Network error', 'danger');
        if (errorCallback) errorCallback(error);
    });
}

// Auto-save for Candidate Exams
function initAutoSave(examId, interval = 20000) {
    setInterval(function() {
        const answers = collectAnswers();
        fetch('candidate/ajax/save_answers.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                '_csrf': getCsrfToken(),
                'exam_id': examId,
                'answers': JSON.stringify(answers),
                'auto_save': true
            })
        })
        .catch(error => console.error('Auto-save failed:', error));
    }, interval);
}

function collectAnswers() {
    // Implementation depends on exam interface structure
    const answers = {};
    document.querySelectorAll('.question-input').forEach(input => {
        answers[input.dataset.questionId] = input.value;
    });
    return answers;
}

// Countdown Timer
function startCountdown(seconds, elementId, onComplete) {
    const element = document.getElementById(elementId);
    let timeLeft = seconds;
    
    const timer = setInterval(function() {
        const minutes = Math.floor(timeLeft / 60);
        const secs = timeLeft % 60;
        element.textContent = `${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
        
        timeLeft--;
        
        if (timeLeft < 0) {
            clearInterval(timer);
            if (onComplete) onComplete();
            
            // Auto-submit exam
            const submitBtn = document.getElementById('submit-exam-btn');
            if (submitBtn) submitBtn.click();
        }
    }, 1000);
    
    return timer;
}

// Image/Audio Preview
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function previewAudio(input, audioId) {
    const audio = document.getElementById(audioId);
    if (input.files && input.files[0]) {
        const url = URL.createObjectURL(input.files[0]);
        audio.src = url;
        audio.controls = true;
        audio.style.display = 'block';
    }
}

// Feedback Anonymous Toggle
function initFeedbackToggle() {
    const anonymousToggle = document.getElementById('anonymous-toggle');
    const identityFields = document.querySelectorAll('.identity-field');
    
    if (anonymousToggle) {
        anonymousToggle.addEventListener('change', function() {
            identityFields.forEach(field => {
                field.style.display = this.checked ? 'none' : 'block';
                field.required = !this.checked;
            });
        });
    }
}

// Company Branding Update
function updateCompanyBranding(logoUrl, companyName, tagline) {
    const logoImg = document.querySelector('.logo-sm, .logo-lg');
    if (logoImg) logoImg.src = logoUrl;
    
    const siteName = document.querySelector('.navbar-brand span');
    if (siteName) siteName.textContent = companyName;
    
    // Update favicon
    const favicon = document.querySelector('link[rel="icon"]');
    if (favicon) favicon.href = logoUrl;
    
    showToast('Branding updated successfully', 'success');
}
