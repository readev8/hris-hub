/**
 * ============================================================================
 * Blueprints Page Specifications
 * ============================================================================
 *
 * Description: Inline specification field editing for design pages
 * Date: 2026-07-16
 * Standard: Mini (<400 lines)
 */

// ===========================
// PUBLIC API (called from onclick in view)
// ===========================

function updateSpecField(el) {
    var row = $(el).closest('tr');
    var specId = row.data('spec-id');
    var field = $(el).data('field');
    var value = $(el).val();
    var data = {};
    data[field] = value;
    $.ajax({
        url: site_url + '/blueprints/page-specifications/' + specId + '/update',
        type: 'POST',
        data: data,
        success: function(res) {
            if (res.status) {
                toastr.success('Updated');
            } else {
                toastr.error(res.message || 'Failed to update');
            }
        },
        error: function() { toastr.error('Update failed'); }
    });
}

function deleteSpec(el) {
    var row = $(el).closest('tr');
    var specId = row.data('spec-id');
    Swal.fire({
        title: 'Delete Specification?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Delete',
        confirmButtonColor: '#AA0808',
        cancelButtonColor: '#758CA4',
    }).then(function(result) {
        if (result.isConfirmed) {
            $.post(site_url + '/blueprints/page-specifications/' + specId + '/delete', {}, function(res) {
                if (res.status) {
                    toastr.success('Specification deleted');
                    row.fadeOut(300, function() { $(this).remove(); updateSpecCount(); });
                } else {
                    toastr.error(res.message || 'Failed');
                }
            });
        }
    });
}

function updateSpecCount() {
    var count = $('#specsContainer tbody tr').length;
    $('#specCountBadge').text(count + ' field' + (count !== 1 ? 's' : ''));
    if (count === 0) {
        $('#specsContainer').html('<div class="sap-empty" style="padding:40px"><i class="fas fa-list-alt" style="font-size:36px"></i><h4>No specifications</h4><p>Add page specifications for this design page.</p></div>');
    }
}

function showAddSpec() {
    $('#specForm')[0].reset();
    $('#specModal').modal('show');
}

// ===========================
// INITIALIZATION
// ===========================

$(function() {
    var data = window.PageData || {};

    $('#specForm').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: site_url + '/blueprints/design-pages/' + data.token + '/page-specifications',
            type: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                if (res.status) {
                    toastr.success('Specification added');
                    $('#specModal').modal('hide');
                    location.reload();
                } else {
                    toastr.error(res.message || 'Failed');
                }
            },
            error: function() { toastr.error('Request failed'); }
        });
    });
});
