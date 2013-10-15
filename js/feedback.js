function feedback()
{
	$('#feedbutton').hide();
	$('#feedback').show();
	$('#feedback-box').show();

	return false;
}

function close_feedback()
{
	$('#feedbutton').show();
	$('#feedback-box').hide();
	$('#feedback').hide();

	return false;
}

function validate_email_feedback(email)
{
    var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;

    return re.test(email);
}

function validate_feedback()
{
	if( $('#feedback-msg').val() )
	{
		$('#feedback-button').removeClass('block');
	}
	else
	{
		$('#feedback-button').addClass('block');
	}
}

function send_feedback()
{
	if( !$('#feedback-msg').val() )
	{
		return false;
	}

	if( !$('#feedback-name').val() )
	{
		if( !$('#feedback-surname').val() )
		{
			alert('Please enter your first and last name');
			return false;
		}

		alert('Please enter your first name');
		return false;
	}

	if( !$('#feedback-surname').val() )
	{
		alert('Please enter your last name');
		return false;
	}

	if( !validate_email_feedback( $("#feedback-email").val() ) )
	{
		alert('Please enter a valid email address');
		return false;
	}

	if( $('#feedback-msg').val() && !$('#feedback-button').hasClass('button-loading') )
	{
		$('#feedback-button').addClass('button-loading');

		$.post('/php/feedback.php', {
			name: $("#feedback-name").val(),
			surname: $("#feedback-surname").val(),
			email: $("#feedback-email").val(),
			msg: $('#feedback-msg').val()
		} , function(data) {

            $('#feedback-button').removeClass('button-loading');

			if(data.result == 'success')
			{
				$('#feedback-msg').val('');
				close_feedback();
			}
			else
			{
    			alert(data.msg);
			}
		}, "json");
	}

	return false;
}

$(document).ready(function(){
	 $('#feedback-msg').keyup(validate_feedback);
});