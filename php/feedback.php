<?php

// HelpScout config
define(HELPSCOUT_ID, "");
define(HELPSCOUT_KEY, "");

if(!HELPSCOUT_ID || !HELPSCOUT_KEY)
{
    die(json_encode(array(
        'result' => 'error',
        'msg' => 'Missing HelpScout configuration'
    )));
}

// ----

include 'HelpScout/ApiClient.php';
use HelpScout\ApiClient;

\HelpScout\ClassLoader::register();

// get POST data
$name = isset($_POST['name']) ? $_POST['name'] : false;
$surname = isset($_POST['surname']) ? $_POST['surname'] : false;
$email = isset($_POST['email']) ? $_POST['email'] : false;
$msg = isset($_POST['msg']) ? $_POST['msg'] : false;

if(!$name || !$surname || !$email || !$msg)
{
    die(json_encode(array(
        'result' => 'error',
        'msg' => 'Missing required fields'
    )));
}

// validate email
if(!filter_var($email, FILTER_VALIDATE_EMAIL))
{
    die(json_encode(array(
        'result' => 'error',
        'msg' => 'Email address is not valid'
    )));
}

// ---

$mailbox = new \HelpScout\model\ref\MailboxRef();
$mailbox->setId(HELPSCOUT_ID);

try {

	$client = ApiClient::getInstance();
	$client->setKey(HELPSCOUT_KEY);

    // check if email is register in HelpScout
	$searchCustomer = $client->searchCustomersByEmail($email);

	if($searchCustomer->getCount())
	{
	    // get customer from HelpScout

		$custormers = $searchCustomer->getItems();

		$customer = $custormers[0];
	}
	else
	{
	    // new customer

		$customer = new \HelpScout\model\Customer();

		$customer->setFirstName($name);
		$customer->setLastName($surname);

		$emailHome = new \HelpScout\model\customer\EmailEntry();
		$emailHome->setValue($email);

		$customer->setEmails(array($emailHome));

		$client->createCustomer($customer);
	}


	$customer_ref = new \HelpScout\model\ref\CustomerRef();
	$customer_ref->setId( $customer->getId() );
	$customer_ref->setEmail($email);

    // create conversation
	$conversation = new \HelpScout\model\Conversation();
	$conversation->setSubject("Message from ".$email);
	$conversation->setMailbox($mailbox);
	$conversation->setCustomer($customer_ref);
	$conversation->setType("email");

    // creat thread
	$thread = new \HelpScout\model\thread\Customer();
	$thread->setType("customer");
	$thread->setBody($msg);
	$thread->setStatus("active");

	$createdBy = new \HelpScout\model\ref\PersonRef();
	$createdBy->setType("customer");
	$createdBy->setId( $customer->getId() );

	$thread->setCreatedBy($createdBy);

	$conversation->setThreads(array($thread));
	$conversation->setCreatedBy($createdBy);

	$client->createConversation($conversation);
}
catch (Exception $e)
{
    die(json_encode(array(
        'result' => 'error',
        'msg' => $e->getMessage()
    )));
}

die(json_encode(array(
    'result' => 'success'
)));
