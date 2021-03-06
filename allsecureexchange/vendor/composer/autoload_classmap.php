<?php

// autoload_classmap.php @generated by Composer

$vendorDir = dirname(dirname(__FILE__));
$baseDir = dirname($vendorDir);

return array(
    'AllsecureExchange\\Client\\Callback\\ChargebackData' => $baseDir . '/client/Callback/ChargebackData.php',
    'AllsecureExchange\\Client\\Callback\\ChargebackReversalData' => $baseDir . '/client/Callback/ChargebackReversalData.php',
    'AllsecureExchange\\Client\\Callback\\Result' => $baseDir . '/client/Callback/Result.php',
    'AllsecureExchange\\Client\\Client' => $baseDir . '/client/Client.php',
    'AllsecureExchange\\Client\\CustomerProfile\\CustomerData' => $baseDir . '/client/CustomerProfile/CustomerData.php',
    'AllsecureExchange\\Client\\CustomerProfile\\DeleteProfileResponse' => $baseDir . '/client/CustomerProfile/DeleteProfileResponse.php',
    'AllsecureExchange\\Client\\CustomerProfile\\GetProfileResponse' => $baseDir . '/client/CustomerProfile/GetProfileResponse.php',
    'AllsecureExchange\\Client\\CustomerProfile\\PaymentData\\CardData' => $baseDir . '/client/CustomerProfile/PaymentData/CardData.php',
    'AllsecureExchange\\Client\\CustomerProfile\\PaymentData\\IbanData' => $baseDir . '/client/CustomerProfile/PaymentData/IbanData.php',
    'AllsecureExchange\\Client\\CustomerProfile\\PaymentData\\PaymentData' => $baseDir . '/client/CustomerProfile/PaymentData/PaymentData.php',
    'AllsecureExchange\\Client\\CustomerProfile\\PaymentData\\WalletData' => $baseDir . '/client/CustomerProfile/PaymentData/WalletData.php',
    'AllsecureExchange\\Client\\CustomerProfile\\PaymentInstrument' => $baseDir . '/client/CustomerProfile/PaymentInstrument.php',
    'AllsecureExchange\\Client\\CustomerProfile\\UpdateProfileResponse' => $baseDir . '/client/CustomerProfile/UpdateProfileResponse.php',
    'AllsecureExchange\\Client\\Data\\CreditCardCustomer' => $baseDir . '/client/Data/CreditCardCustomer.php',
    'AllsecureExchange\\Client\\Data\\Customer' => $baseDir . '/client/Data/Customer.php',
    'AllsecureExchange\\Client\\Data\\Data' => $baseDir . '/client/Data/Data.php',
    'AllsecureExchange\\Client\\Data\\IbanCustomer' => $baseDir . '/client/Data/IbanCustomer.php',
    'AllsecureExchange\\Client\\Data\\Item' => $baseDir . '/client/Data/Item.php',
    'AllsecureExchange\\Client\\Data\\Request' => $baseDir . '/client/Data/Request.php',
    'AllsecureExchange\\Client\\Data\\Result\\CreditcardData' => $baseDir . '/client/Data/Result/CreditcardData.php',
    'AllsecureExchange\\Client\\Data\\Result\\IbanData' => $baseDir . '/client/Data/Result/IbanData.php',
    'AllsecureExchange\\Client\\Data\\Result\\PhoneData' => $baseDir . '/client/Data/Result/PhoneData.php',
    'AllsecureExchange\\Client\\Data\\Result\\ResultData' => $baseDir . '/client/Data/Result/ResultData.php',
    'AllsecureExchange\\Client\\Data\\Result\\WalletData' => $baseDir . '/client/Data/Result/WalletData.php',
    'AllsecureExchange\\Client\\Exception\\ClientException' => $baseDir . '/client/Exception/ClientException.php',
    'AllsecureExchange\\Client\\Exception\\InvalidValueException' => $baseDir . '/client/Exception/InvalidValueException.php',
    'AllsecureExchange\\Client\\Exception\\RateLimitException' => $baseDir . '/client/Exception/RateLimitException.php',
    'AllsecureExchange\\Client\\Exception\\TimeoutException' => $baseDir . '/client/Exception/TimeoutException.php',
    'AllsecureExchange\\Client\\Exception\\TypeException' => $baseDir . '/client/Exception/TypeException.php',
    'AllsecureExchange\\Client\\Http\\ClientInterface' => $baseDir . '/client/Http/ClientInterface.php',
    'AllsecureExchange\\Client\\Http\\CurlClient' => $baseDir . '/client/Http/CurlClient.php',
    'AllsecureExchange\\Client\\Http\\CurlExec' => $baseDir . '/client/Http/CurlExec.php',
    'AllsecureExchange\\Client\\Http\\Exception\\ClientException' => $baseDir . '/client/Http/Exception/ClientException.php',
    'AllsecureExchange\\Client\\Http\\Exception\\ResponseException' => $baseDir . '/client/Http/Exception/ResponseException.php',
    'AllsecureExchange\\Client\\Http\\Response' => $baseDir . '/client/Http/Response.php',
    'AllsecureExchange\\Client\\Http\\ResponseInterface' => $baseDir . '/client/Http/ResponseInterface.php',
    'AllsecureExchange\\Client\\Json\\DataObject' => $baseDir . '/client/Json/DataObject.php',
    'AllsecureExchange\\Client\\Json\\ErrorResponse' => $baseDir . '/client/Json/ErrorResponse.php',
    'AllsecureExchange\\Client\\Json\\ResponseObject' => $baseDir . '/client/Json/ResponseObject.php',
    'AllsecureExchange\\Client\\Schedule\\ScheduleData' => $baseDir . '/client/Schedule/ScheduleData.php',
    'AllsecureExchange\\Client\\Schedule\\ScheduleError' => $baseDir . '/client/Schedule/ScheduleError.php',
    'AllsecureExchange\\Client\\Schedule\\ScheduleResult' => $baseDir . '/client/Schedule/ScheduleResult.php',
    'AllsecureExchange\\Client\\StatusApi\\StatusRequestData' => $baseDir . '/client/StatusApi/StatusRequestData.php',
    'AllsecureExchange\\Client\\StatusApi\\StatusResult' => $baseDir . '/client/StatusApi/StatusResult.php',
    'AllsecureExchange\\Client\\Transaction\\Base\\AbstractTransaction' => $baseDir . '/client/Transaction/Base/AbstractTransaction.php',
    'AllsecureExchange\\Client\\Transaction\\Base\\AbstractTransactionWithReference' => $baseDir . '/client/Transaction/Base/AbstractTransactionWithReference.php',
    'AllsecureExchange\\Client\\Transaction\\Base\\AddToCustomerProfileInterface' => $baseDir . '/client/Transaction/Base/AddToCustomerProfileInterface.php',
    'AllsecureExchange\\Client\\Transaction\\Base\\AddToCustomerProfileTrait' => $baseDir . '/client/Transaction/Base/AddToCustomerProfileTrait.php',
    'AllsecureExchange\\Client\\Transaction\\Base\\AmountableInterface' => $baseDir . '/client/Transaction/Base/AmountableInterface.php',
    'AllsecureExchange\\Client\\Transaction\\Base\\AmountableTrait' => $baseDir . '/client/Transaction/Base/AmountableTrait.php',
    'AllsecureExchange\\Client\\Transaction\\Base\\ItemsInterface' => $baseDir . '/client/Transaction/Base/ItemsInterface.php',
    'AllsecureExchange\\Client\\Transaction\\Base\\ItemsTrait' => $baseDir . '/client/Transaction/Base/ItemsTrait.php',
    'AllsecureExchange\\Client\\Transaction\\Base\\OffsiteInterface' => $baseDir . '/client/Transaction/Base/OffsiteInterface.php',
    'AllsecureExchange\\Client\\Transaction\\Base\\OffsiteTrait' => $baseDir . '/client/Transaction/Base/OffsiteTrait.php',
    'AllsecureExchange\\Client\\Transaction\\Base\\ScheduleInterface' => $baseDir . '/client/Transaction/Base/ScheduleInterface.php',
    'AllsecureExchange\\Client\\Transaction\\Base\\ScheduleTrait' => $baseDir . '/client/Transaction/Base/ScheduleTrait.php',
    'AllsecureExchange\\Client\\Transaction\\Capture' => $baseDir . '/client/Transaction/Capture.php',
    'AllsecureExchange\\Client\\Transaction\\Debit' => $baseDir . '/client/Transaction/Debit.php',
    'AllsecureExchange\\Client\\Transaction\\Deregister' => $baseDir . '/client/Transaction/Deregister.php',
    'AllsecureExchange\\Client\\Transaction\\Error' => $baseDir . '/client/Transaction/Error.php',
    'AllsecureExchange\\Client\\Transaction\\Payout' => $baseDir . '/client/Transaction/Payout.php',
    'AllsecureExchange\\Client\\Transaction\\Preauthorize' => $baseDir . '/client/Transaction/Preauthorize.php',
    'AllsecureExchange\\Client\\Transaction\\Refund' => $baseDir . '/client/Transaction/Refund.php',
    'AllsecureExchange\\Client\\Transaction\\Register' => $baseDir . '/client/Transaction/Register.php',
    'AllsecureExchange\\Client\\Transaction\\Result' => $baseDir . '/client/Transaction/Result.php',
    'AllsecureExchange\\Client\\Transaction\\VoidTransaction' => $baseDir . '/client/Transaction/VoidTransaction.php',
    'AllsecureExchange\\Client\\Xml\\Generator' => $baseDir . '/client/Xml/Generator.php',
    'AllsecureExchange\\Client\\Xml\\Parser' => $baseDir . '/client/Xml/Parser.php',
    'AllsecureExchange\\Prestashop\\PaymentMethod\\CreditCard' => $baseDir . '/payment_method/CreditCard.php',
    'AllsecureExchange\\Prestashop\\PaymentMethod\\PaymentMethodInterface' => $baseDir . '/payment_method/PaymentMethodInterface.php',
);
