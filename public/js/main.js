$(document).ready(function(){
	initFocus();
	showLoginForm();

	$('table.even_odd tbody tr:even').css('background-color', '#f5f5f2');

	$(".multiselect").multiselect({
		header: false,
		selectedList: 1
	});

	$('.rating_stars_dinamic li a').hover(function(){
		$(this).addClass('hover_star');
		$(this).parent().prevAll().children().addClass('hover_star');
	}, function(){
		$(this).removeClass('hover_star');
		$(this).parent().prevAll().children().removeClass('hover_star');
	});
});

function initFocus(){
	$(document).on("focus", "input[type='text'], input[type='password'], textarea", function(){
		$(this)
			.parents('.input_row')
			.find('.watermark')
			.hide();
	});

	$(document).on("blur", "input[type='text'], input[type='password'], textarea", function(){
		if($(this).val() == ''){
			$(this)
				.parents('.input_row')
				.find('.watermark')
				.show();
		}
	});

	$(document).on("click", ".watermark", function(){
		$(this).hide();
		$(this)
			.parent('.input_row')
			.find("input[type='text'], textarea")
			.focus();
	});

	$(document).on("each", "input[type='text'], input[type='password'], textarea", function(){
		if($(this).val() !== ''){
			$(this)
				.parents('.input_row')
				.find('.watermark')
				.hide();
		}
	});
}

function showLoginForm() {
	$('#login').click(function(e) {
		var $message = $('.login_form');

		if ($message.css('display') != 'block') {
			$message.show();
			
			var firstClick = true;
			$(document).bind('click.myEvent', function(e) {
				if (!firstClick && $(e.target).closest('.login_form').length == 0) {
					$message.hide();
					$(document).unbind('click.myEvent');
				}
				firstClick = false;
			});
		}
	});
}