<?php

namespace SS6\ShopBundle\Controller\Front;

use SS6\ShopBundle\Form\Front\Customer\CustomerFormType;
use SS6\ShopBundle\Model\Customer\CustomerData;
use SS6\ShopBundle\Model\Security\Roles;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class CustomerController extends Controller {

	public function editAction(Request $request) {
		$flashMessageText = $this->get('ss6.shop.flash_message.text_sender.front');
		/* @var $flashMessageText \SS6\ShopBundle\Model\FlashMessage\TextSender */
		
		if (!$this->get('security.context')->isGranted(Roles::ROLE_CUSTOMER)) {
			$flashMessageText->addError('Pro přístup na tuto stránku musíte být přihlášeni');
			return $this->redirect($this->generateUrl('front_login'));
		}

		$user = $this->getUser();

		$form = $this->createForm(new CustomerFormType());

		$customerData = new CustomerData();

		if (!$form->isSubmitted()) {
			$customerData->setFromEntity($user);
		}

		$form->setData($customerData);
		$form->handleRequest($request);

		if ($form->isValid()) {
			$customerEditFacade = $this->get('ss6.shop.customer.customer_edit_facade');
			/* @var $customerEditFacade \SS6\ShopBundle\Model\Customer\CustomerEditFacade */

			$customerData = $form->getData();
			$user = $customerEditFacade->editByCustomer(
				$user->getId(),
				$customerData
			);

			$flashMessageText->addSuccess('Vaše údaje byly úspěšně zaktualizovány');
			return $this->redirect($this->generateUrl('front_customer_edit'));
		}

		if ($form->isSubmitted() && !$form->isValid()) {
			$flashMessageText->addError('Prosím zkontrolujte si správnost vyplnění všech údajů');
		}

		return $this->render('@SS6Shop/Front/Content/Customer/edit.html.twig', array(
			'form' => $form->createView(),
		));
	}

}
