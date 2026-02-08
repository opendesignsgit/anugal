(function($) {
	$(document).ready(function() {
		$('.duplicate_group').click(function(){
			var term_id = $(this).attr("data-term_id");
			var taxonomy = $(this).attr("data-taxonomy");
			var post_type = $(this).attr("data-post_type");
			$.ajax({
				type: 'POST',
				url: admin_script.ajaxurl,
				data: {"action":"duplicate_group","term_id":term_id,taxonomy:taxonomy,post_type:post_type},
				beforeSend: function() {
				},
				success: function (response) {
					console.log(response);
					window.location.reload();
					/* filter[0].reset();
					filter.find("#loader").css('display','none');
					filter.find('input[type="submit"]').prop('disabled', false); */
				},
				error: function (response) {
					console.log(response); 
					/* filter[0].reset();
					filter.find("#loader").css('display','none');
					filter.find('input[type="submit"]').prop('disabled', false); */
				}
			}); 
		});
		$('.delete_group').click(function(){
			var term_id = $(this).attr("data-term_id");
			var taxonomy = $(this).attr("data-taxonomy");
			var post_type = $(this).attr("data-post_type");
			var result = confirm("Want to delete?");
			if (result) {
				$.ajax({
					type: 'POST',
					url: admin_script.ajaxurl,
					data: {"action":"delete_group","term_id":term_id,taxonomy:taxonomy,post_type:post_type},
					beforeSend: function() {
					},
					success: function (response) {
						console.log(response);
						window.location.reload();
						/* filter[0].reset();
						filter.find("#loader").css('display','none');
						filter.find('input[type="submit"]').prop('disabled', false); */
					},
					error: function (response) {
						console.log(response); 
						/* filter[0].reset();
						filter.find("#loader").css('display','none');
						filter.find('input[type="submit"]').prop('disabled', false); */
					}
				});
			}
		});
	});
})(jQuery);

jQuery(document).ready(function($) {
    $('td._projects_order').dblclick(function() {
        var $cell = $(this);
        var oldValue = $cell.text();
        $cell.empty().append($('<input>', { value: oldValue })).find('input').focus();
    });

    $(document).on('keypress', 'td._projects_order input', function(e) {
        var $input = $(this);
        if (e.which == 13) {
			e.preventDefault();
            var $cell = $input.closest('td');
            var newValue = $input.val();
            var column = '_projects_order';
            var postId = $cell.closest('tr').attr('id').replace('post-', '');
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'save_editable_cell',
                    column: column,
                    newValue: newValue,
                    postId: postId
                },
                success: function(response) {
                    $cell.text(newValue);
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                }
            });
        }
    });
});

