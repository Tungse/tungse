var timeOutEyebrows = null;
var timeOutFace     = null;

$(function()
{
	timeOutEyebrows = setTimeout(function(){ eyebrowsAnimation(); }, 1000);
	timeOutFace     = setTimeout(function(){ faceRotation(); }, 15000);
	
	$(document).on('keyup', '.input-comment', function(event)
	{
		if(event.keyCode == 13) setComment();
	});
	
	$(document).on('click', '.button', function()
	{
		setComment();
	});
});

function setComment()
{
	var content = $('.input-comment').val();
	
	if(content == '') return;
	
	$.post('?a=setComment', {content: content}, function(response)
	{
		$('.holder-comments').prepend(response);
		$('.input-comment').fadeOut(200, function()
		{
			$(this).val('').fadeIn(200);
		});
	});
}

function eyebrowsAnimation()
{
	clearTimeout(timeOutEyebrows);
	
	$('.eyebrows').animate({top: '-=20'}, 50, 
	function()
	{
		$('.eyebrows').animate({top: '+=20'}, 150, 
		function()
		{
			setTimeout(function()
			{
				$('.eyebrows').animate({top: '-=18'}, 50, 
				function()
				{
					$('.eyebrows').animate({top: '+=18'}, 150, 
					function()
					{
						timeOutEyebrows = setTimeout(function(){ eyebrowsAnimation(); }, 2000);
					});
				});
			}, 150);
		});
	});
}

function faceRotation()
{
	clearTimeout(timeOutFace);
	
	$('.content').fadeOut(200, function()
	{
		$('.face').fadeIn(200, function()
		{
			$('.face').rotate(
			{
				angle: 0, 
				animateTo: 720,
				duration: 4000,
				easing: $.easing.easeInOutElastic,
				callback: function() 
				{
					$('.face').fadeOut(200, function()
					{
						$('.content').fadeIn(200, function()
						{
							timeOutFace = setTimeout(function(){ faceRotation(); }, 15000);
						});
					});
				}
			});
		});
	});
}