const itiInstances = new Map();

const mobileExamples = {
    bd: "+8801790000000",
    in: "+919000000000",
    us: "+12025550123",
    ar: "+5491123456789",
    br: "+5511912345678",
    mx: "+5215512345678",
    co: "+573001234567",
    id: "+628123456789",
    ng: "+2348012345678",
    pk: "+923001234567",
    eg: "+201001234567",
    tr: "+905301234567",
    ru: "+79261234567",
    za: "+27821234567",
    ph: "+639171234567",
    vn: "+84981234567",
    ir: "+989121234567",
    sa: "+966501234567",
    iq: "+9647712345678",
    ke: "+254712345678",
    et: "+251911234567"
};

function initializeIntlTelInput() {
    const inputs = document.querySelectorAll(
        'input[type="tel"]:not([data-intl-initialized])'
    );

    inputs.forEach(input => {
        const iti = window.intlTelInput(input, {
            initialCountry: "bd",
            autoInsertDialCode: false,
            nationalMode: false,
            formatOnDisplay: false,
            separateDialCode: false,
            showSelectedDialCode: true,
            autoPlaceholder: "off",
            utilsScript:
                "https://cdn.jsdelivr.net/npm/intl-tel-input@19.2.15/build/js/utils.js"
        });

        // Override _setFlag after init
        const originalSetFlag = iti._setFlag;
        iti._setFlag = function(countryCode) {
            const result = originalSetFlag.call(this, countryCode);

            if (this.options.showSelectedDialCode && this.isRTL) {
                const selectedFlagWidth =
                    this.selectedFlag.offsetWidth ||
                    this._getHiddenSelectedFlagWidth();
                this.telInput.style.paddingLeft = `${selectedFlagWidth + 6}px`;
                this.telInput.style.paddingRight = "";
            }

            return result;
        };

        iti._setFlag(iti.getSelectedCountryData().iso2);

        // itiInstances.set(input, iti);
        // input.setAttribute("data-intl-initialized", "true");

        // ✅ Move name & value to a hidden input
        const originalName = input.getAttribute('name');
        const originalValue = input.value;
        input.removeAttribute('name'); // remove name from visible input

        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = originalName;
        input.parentNode.appendChild(hiddenInput);

        // ✅ Set initial value if exists
        if (originalValue) {
            iti.setNumber(originalValue);
            hiddenInput.value = iti.getNumber(); // formatted full number
        }

        // ✅ Keep updating hidden input on user change
        input.addEventListener('input', () => {
            hiddenInput.value = iti.getNumber();
        });

        input.addEventListener('countrychange', () => {
            hiddenInput.value = iti.getNumber();
        });

        iti._setFlag(iti.getSelectedCountryData().iso2);
        itiInstances.set(input, iti);
        input.setAttribute("data-intl-initialized", "true");
    });
}

function keepOnlyNumbers(str) {
    return str.replace(/\D/g, "");
}

function getLocalExampleNumberLength(iti) {
    try {
        const iso2 = iti.getSelectedCountryData().iso2;
        const dialCode = iti.getSelectedCountryData().dialCode;

        let example = mobileExamples[iso2]
            ? mobileExamples[iso2]
            : intlTelInputUtils.getExampleNumber(
                  iso2,
                  true,
                  intlTelInputUtils.numberFormat.E164
              );

        const digitsOnly = example.replace(/\D/g, "");

        // Dynamically find position of dialCode inside number
        const dialCodeIndex = digitsOnly.indexOf(dialCode);
        const nationalDigits =
            dialCodeIndex >= 0
                ? digitsOnly.slice(dialCodeIndex + dialCode.length)
                : digitsOnly;

        return nationalDigits.length;
    } catch (e) {
        console.warn("Fallback to default local max length (12)", e);
        return 12;
    }
}

function validatePhoneInput(input) {
    const iti = itiInstances.get(input);
    if (!iti || typeof iti.isValidNumber !== "function") return false;

    const fullNumber = iti.getNumber(); // E164 format
    const dialCode = iti.getSelectedCountryData().dialCode;
    const digitsOnly = keepOnlyNumbers(fullNumber);
    const dialCodeIndex = digitsOnly.indexOf(dialCode);
    const localNumber =
        dialCodeIndex >= 0
            ? digitsOnly.slice(dialCodeIndex + dialCode.length)
            : digitsOnly;

    const expectedLength = getLocalExampleNumberLength(iti);
    const isValid = iti.isValidNumber() && localNumber.length === expectedLength;

    input.classList.toggle("is-valid", isValid);
    input.classList.toggle("is-invalid", !isValid);

    return isValid;
}

document.addEventListener("DOMContentLoaded", () => {
    initializeIntlTelInput();

    document.addEventListener("input", function(e) {
        if (e.target.matches('input[type="tel"]')) {
            const input = e.target;
            const iti = itiInstances.get(input);
            if (!iti) return;

            let inputVal = keepOnlyNumbers(input.value);
            const dialCode = iti.getSelectedCountryData().dialCode;
            const expectedLength = getLocalExampleNumberLength(iti) + 3;

            const dialCodeIndex = inputVal.indexOf(dialCode);
            let localPart =
                dialCodeIndex >= 0
                    ? inputVal.slice(dialCodeIndex + dialCode.length)
                    : inputVal;

            if (localPart.length + 4 > expectedLength) {
                localPart = localPart.slice(0, expectedLength);
            }

            input.value = localPart;

            const hiddenInput = input.parentNode.querySelector('input[type="hidden"]');
            if (hiddenInput && iti) {
                hiddenInput.value = iti.getNumber();
            }

            validatePhoneInput(input);
        }
    });

    $(document).on("shown.bs.modal", function() {
        initializeIntlTelInput();
    });
});
