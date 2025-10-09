document.addEventListener('DOMContentLoaded', function() {
    var nestedSortables = [].slice.call(document.querySelectorAll('.sortable-list'));

    for (var i = 0; i < nestedSortables.length; i++) {
        new Sortable(nestedSortables[i], {
            group: 'nested',
            animation: 150,
            fallbackOnBody: true,
            swapThreshold: 0.65,
            onEnd: function (evt) {
                var itemEl = evt.item;
                var newIndex = evt.newIndex;
                var newParentId = evt.to.closest('li') ? evt.to.closest('li').dataset.id : 'root';

                fetch('/admin/products/updateTree', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': csrfToken // Make sure to set this variable with your CSRF token
                    },
                    body: JSON.stringify({
                        id: itemEl.dataset.id,
                        newIndex: newIndex,
                        newParentId: newParentId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log('Tree updated successfully');
                    } else {
                        console.error('Error updating tree');
                    }
                })
                .catch((error) => {
                    console.error('Error:', error);
                });
            }
        });
    }
});