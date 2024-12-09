// Initialisation de intl-tel-input sur le champ de numéro de téléphone
const phoneInputLogin = document.querySelector("#username");
const iti = window.intlTelInput(phoneInputLogin, {
    initialCountry: "cd",  // Pays par défaut
    preferredCountries: ["cd", "fr", "us", "gb"],
    utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js"
});

function updatePhoneNumberLogin() {
    phoneInputLogin.value = iti.getNumber().substring(1);
}

phoneInputLogin.addEventListener("countrychange", updatePhoneNumberLogin);
phoneInputLogin.addEventListener("blur", updatePhoneNumberLogin);


//transport

const phoneInput = document.querySelector("#wallet_operation_payerAccountNumber");
const iti = window.intlTelInput(phoneInput, {
    initialCountry: "cd",  // Pays par défaut
    preferredCountries: ["cd", "fr", "us", "gb"],
    utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js"
});

function updatePhoneNumber() {
    phoneInput.value = iti.getNumber().substring(1);
}

phoneInput.addEventListener("countrychange", updatePhoneNumber);
phoneInput.addEventListener("blur", updatePhoneNumber);


//parking

const phoneInput = document.querySelector("#subscription_form_payerAccountNumber");
const iti = window.intlTelInput(phoneInput, {
    initialCountry: "cd",  // Pays par défaut
    preferredCountries: ["cd", "fr", "us", "gb"],
    utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js"
});

function updatePhoneNumber() {
    phoneInput.value = iti.getNumber().substring(1);
}

phoneInput.addEventListener("countrychange", updatePhoneNumber);
phoneInput.addEventListener("blur", updatePhoneNumber);

//register

const phoneInput = document.querySelector("#registration_form_username");
const iti = window.intlTelInput(phoneInput, {
    initialCountry: "cd",  // Pays par défaut
    preferredCountries: ["cd", "fr", "us", "gb"],
    utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js"
});

function updatePhoneNumber() {
    phoneInput.value = iti.getNumber().substring(1);
}

phoneInput.addEventListener("countrychange", updatePhoneNumber);
phoneInput.addEventListener("blur", updatePhoneNumber);