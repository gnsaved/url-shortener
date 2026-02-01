// Modal functions
function showCreateModal() {
    document.getElementById('createModal').classList.add('show');
}

function closeCreateModal() {
    document.getElementById('createModal').classList.remove('show');
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('createModal');
    if (event.target === modal) {
        closeCreateModal();
    }
}

// Select all checkboxes
document.getElementById('select-all')?.addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.url-table tbody input[type="checkbox"]');
    checkboxes.forEach(cb => cb.checked = this.checked);
});

// Copy URL to clipboard
function copyToClipboard(url) {
    navigator.clipboard.writeText(url).then(() => {
        alert('URL copied to clipboard!');
    }).catch(err => {
        console.error('Failed to copy:', err);
    });
}

// Share buttons
document.querySelectorAll('.share-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        const row = this.closest('tr');
        const shortUrl = row.querySelector('.short-link').href;
        
        if (this.classList.contains('facebook')) {
            window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shortUrl)}`, '_blank');
        } else if (this.classList.contains('twitter')) {
            window.open(`https://twitter.com/intent/tweet?url=${encodeURIComponent(shortUrl)}`, '_blank');
        } else {
            copyToClipboard(shortUrl);
        }
    });
});

// Show success message if URL was created
window.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('success')) {
        const newUrl = urlParams.get('url');
        if (newUrl) {
            const msg = document.createElement('div');
            msg.className = 'alert alert-success';
            msg.style.position = 'fixed';
            msg.style.top = '20px';
            msg.style.right = '20px';
            msg.style.zIndex = '9999';
            msg.innerHTML = `✅ Short URL created: <strong>${newUrl}</strong>`;
            document.body.appendChild(msg);
            
            setTimeout(() => msg.remove(), 5000);
        }
    }
});
