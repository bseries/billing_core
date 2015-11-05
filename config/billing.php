<?php
/**
 * Billing Core
 *
 * Copyright (c) 2014 Atelier Disko - All rights reserved.
 *
 * Licensed under the AD General Software License v1.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *
 * You should have received a copy of the AD General Software
 * License. If not, see http://atelierdisko.de/licenses.
 */

namespace billing_core\config;

use billing_core\models\PaymentMethods;
use billing_core\models\TaxTypes;
use lithium\g11n\Message;

extract(Message::aliases());

//
// Tax Types
//
// Tax that applies on all goods when business resides in Germany.
// B2B & B2C
TaxTypes::register('DE.vat.standard', [
	'rate' => 19,
	'title' => function() use ($t) {
		return $t('VAT', ['scope' => 'billing_core']);
	},
	'note' => function() use ($t) {
		return $t('Includes 19% VAT.', ['scope' => 'billing_core']);
	}
]);

// Tax that applies on certain goods when business resides in Germany.
TaxTypes::register('DE.vat.reduced', [
	'rate' => 7,
	'title' => function() use ($t) {
		return $t('red. VAT', ['scope' => 'billing_core']);
	},
	'note' => function() use ($t) {
		return $t('Includes 7% VAT.', ['scope' => 'billing_core']);
	}
]);

// Applies under certain circumstances worldwide.
TaxTypes::register('*.vat.reverse', [
	'rate' => false,
	'title' => function() use ($t) {
		return $t('VAT Reverse Charge', ['scope' => 'billing_core']);
	},
	'note' => function() use ($t) {
		return $t('Reverse Charge.', ['scope' => 'billing_core']);
	}
]);

//
// Payment Methods
//
$infoBankAccount = function($context, $format) {
	$data = Settings::read('billing.bankAccount');

	$result   = [];
	$result[] = $data['holder'];
	$result[] = $data['bank'];
	$result[] = null;
	$result[] = "IBAN {$data['iban']}";
	$result[] = "BIC {$data['bic']}";

	if ($format === 'html') {
		return '<pre>' . implode("\n", $result) . '</pre>';
	}
	return implode("\n", $result);
};

PaymentMethods::register('banque.localInvoice', [
	'title' => function() use ($t) {
		return $t('Invoice', ['scope' => 'billing_core']);
	},
	'info' => function($context, $format) use ($t, $infoBankAccount) {
		$output = '';

		if ($context === 'checkout.payment') {
			$intro = 'Nach Erhalt der Rechnung überweisen Sie den Gesamtbetrag auf unser Bankkonto.';
		} else {
			$intro = 'Überweisen Sie den noch offenen Gesamtbetrag nun auf folgendes Konto:';
		}
		if ($format === 'html') {
			$output .= "<p>{$intro}</p>";
		} else {
			$output .= "\n{$intro}\n\n";
		}
		if ($context != 'checkout.payment') {
			$output .= $infoBankAccount($context, $format);
		}
		return $output;
	}
]);

PaymentMethods::register('banque.localPrepayment', [
	'title' => function() use ($t) {
		return $t('Prepayment', ['scope' => 'billing_core']);
	},
	'online' => false,
	'info' => function($context, $format) use ($t, $infoBankAccount) {
		$output = '';

		if ($context === 'checkout.payment') {
			$intro = 'Nach Bestätigung der Bestellung können Sie den Betrag überweisen.';
		} else {
			$intro  = 'Überweisen Sie den Gesamtbetrag unter Nennung Ihrer Bestellnummer auf folgendes Konto. ';
			$intro .= 'Sobald wir Ihre Zahlung erhalten und verbucht haben, senden wir Ihnen eine Zahlungsbestätigung per E–Mail ';
			$intro .= 'und geben Ihre Bestellung umgehend in den Versand.';
		}
		if ($format === 'html') {
			$output .= "<p>{$intro}</p>";
		} else {
			$output .= "\n{$intro}\n\n";
		}

		if ($context != 'checkout.payment') {
			$output .= $infoBankAccount($context, $format);
		}
		return $output;
	}
]);

// https://developer.paypal.com/docs/classic/paypal-payments-standard/integration-guide/formbasics/
/*
PaymentMethods::register('paypal.form', [
	'title' => $t('PayPal'),
	'online' => 'off-site',
	'info' => function($context, $format, $renderer, $order) {
		if ($context === 'checkout.payment') {
			$intro  = 'Nach Bestätigung der Bestellung können Sie den Betrag über PayPal bezahlen.';
			$intro .= ' Sie haben bei PayPal auch die Möglichkeit per Lastschrift oder Kredikarte zu zahlen.';
		} else {
			$intro = 'Sie können nun die Bezahlung per PayPal über folgenden Link vornehmen:';
		}
		$output = $format == 'html' ? "<p>{$intro}</p>" : $intro;

		if ($context != 'checkout.payment') {
			if ($format === 'html') {
				$output .= $renderer->html-link([
					'controller' => 'Orders', 'action' => 'pay', 'uuid' => $order->uuid
				], ['absolute' => true]);
			} else {
				$output .= "\n" . $renderer->url([
					'controller' => 'Orders', 'action' => 'pay', 'uuid' => $order->uuid
				], ['absolute' => true]);
			}
		}
		return $output;
	}
]);
 */

?>