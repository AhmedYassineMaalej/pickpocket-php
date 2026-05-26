
//Settings Password Form Validation
document.addEventListener("DOMContentLoaded", function () {

    const oldPass = document.getElementById('old_password');
    const newPass = document.getElementById('new_password');
    const saveBtn = document.getElementById('saveBtn');

    if (oldPass && newPass && saveBtn) {
        function toggleButtonState() {
            if (oldPass.value.trim() === "" || newPass.value.trim() === "") {
                saveBtn.disabled = true;
            } else {
                saveBtn.disabled = false;
            }
        }

        oldPass.addEventListener('input', toggleButtonState);
        newPass.addEventListener('input', toggleButtonState);
    }
});

function deleteBookmarkFromDb(buttonElement, bookmarkId) {
    const iconImg = buttonElement.querySelector('.bookmark-icon');
    
    if (iconImg) {
        iconImg.src = "/bookmark-empty.svg";
    }

    fetch('/bookmarks/remove', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ bookmarks_item_id: bookmarkId })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network server dropped request handler.');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            const targetCard = document.getElementById(`bookmark-card-${bookmarkId}`);
            if (targetCard) {
                targetCard.style.transition = 'all 0.35s cubic-bezier(0.4, 0, 0.2, 1)';
                targetCard.style.opacity = '0';
                targetCard.style.transform = 'scale(0.85) translateY(5px)';
                
                setTimeout(() => {
                    targetCard.remove();
                    if (document.querySelectorAll('[id^="bookmark-card-"]').length === 0) {
                        window.location.reload();
                    }
                }, 350);
            }
        } else {
            if (iconImg) iconImg.src = "/bookmark-full.svg";
            alert('Error: ' + (data.error || 'Failed to remove entry record.'));
        }
    })
    .catch(err => {
        console.error('Network sync failure:', err);
        if (iconImg) iconImg.src = "/bookmark-full.svg";
        alert('Network connection failure. Could not update your bookmarks catalog.');
    });
}