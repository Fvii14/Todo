const countries = [
    { code: 'ES', name: 'España', prefix: '+34', flag: '🇪🇸' },
    { code: 'US', name: 'Estados Unidos', prefix: '+1', flag: '🇺🇸' },
    { code: 'MX', name: 'México', prefix: '+52', flag: '🇲🇽' },
    { code: 'AR', name: 'Argentina', prefix: '+54', flag: '🇦🇷' },
    { code: 'CO', name: 'Colombia', prefix: '+57', flag: '🇨🇴' },
    { code: 'PE', name: 'Perú', prefix: '+51', flag: '🇵🇪' },
    { code: 'VE', name: 'Venezuela', prefix: '+58', flag: '🇻🇪' },
    { code: 'CL', name: 'Chile', prefix: '+56', flag: '🇨🇱' },
    { code: 'EC', name: 'Ecuador', prefix: '+593', flag: '🇪🇨' },
    { code: 'GT', name: 'Guatemala', prefix: '+502', flag: '🇬🇹' },
    { code: 'CU', name: 'Cuba', prefix: '+53', flag: '🇨🇺' },
    { code: 'BO', name: 'Bolivia', prefix: '+591', flag: '🇧🇴' },
    { code: 'DO', name: 'República Dominicana', prefix: '+1', flag: '🇩🇴' },
    { code: 'HN', name: 'Honduras', prefix: '+504', flag: '🇭🇳' },
    { code: 'PY', name: 'Paraguay', prefix: '+595', flag: '🇵🇾' },
    { code: 'SV', name: 'El Salvador', prefix: '+503', flag: '🇸🇻' },
    { code: 'NI', name: 'Nicaragua', prefix: '+505', flag: '🇳🇮' },
    { code: 'CR', name: 'Costa Rica', prefix: '+506', flag: '🇨🇷' },
    { code: 'PA', name: 'Panamá', prefix: '+507', flag: '🇵🇦' },
    { code: 'UY', name: 'Uruguay', prefix: '+598', flag: '🇺🇾' },
    { code: 'GQ', name: 'Guinea Ecuatorial', prefix: '+240', flag: '🇬🇶' },
    { code: 'FR', name: 'Francia', prefix: '+33', flag: '🇫🇷' },
    { code: 'DE', name: 'Alemania', prefix: '+49', flag: '🇩🇪' },
    { code: 'IT', name: 'Italia', prefix: '+39', flag: '🇮🇹' },
    { code: 'GB', name: 'Reino Unido', prefix: '+44', flag: '🇬🇧' },
    { code: 'PT', name: 'Portugal', prefix: '+351', flag: '🇵🇹' },
    { code: 'NL', name: 'Países Bajos', prefix: '+31', flag: '🇳🇱' },
    { code: 'BE', name: 'Bélgica', prefix: '+32', flag: '🇧🇪' },
    { code: 'CH', name: 'Suiza', prefix: '+41', flag: '🇨🇭' },
    { code: 'AT', name: 'Austria', prefix: '+43', flag: '🇦🇹' },
    { code: 'SE', name: 'Suecia', prefix: '+46', flag: '🇸🇪' },
    { code: 'NO', name: 'Noruega', prefix: '+47', flag: '🇳🇴' },
    { code: 'DK', name: 'Dinamarca', prefix: '+45', flag: '🇩🇰' },
    { code: 'FI', name: 'Finlandia', prefix: '+358', flag: '🇫🇮' },
    { code: 'PL', name: 'Polonia', prefix: '+48', flag: '🇵🇱' },
    { code: 'CZ', name: 'República Checa', prefix: '+420', flag: '🇨🇿' },
    { code: 'HU', name: 'Hungría', prefix: '+36', flag: '🇭🇺' },
    { code: 'RO', name: 'Rumania', prefix: '+40', flag: '🇷🇴' },
    { code: 'BG', name: 'Bulgaria', prefix: '+359', flag: '🇧🇬' },
    { code: 'HR', name: 'Croacia', prefix: '+385', flag: '🇭🇷' },
    { code: 'SI', name: 'Eslovenia', prefix: '+386', flag: '🇸🇮' },
    { code: 'SK', name: 'Eslovaquia', prefix: '+421', flag: '🇸🇰' },
    { code: 'LT', name: 'Lituania', prefix: '+370', flag: '🇱🇹' },
    { code: 'LV', name: 'Letonia', prefix: '+371', flag: '🇱🇻' },
    { code: 'EE', name: 'Estonia', prefix: '+372', flag: '🇪🇪' },
    { code: 'IE', name: 'Irlanda', prefix: '+353', flag: '🇮🇪' },
    { code: 'GR', name: 'Grecia', prefix: '+30', flag: '🇬🇷' },
    { code: 'CY', name: 'Chipre', prefix: '+357', flag: '🇨🇾' },
    { code: 'MT', name: 'Malta', prefix: '+356', flag: '🇲🇹' },
    { code: 'LU', name: 'Luxemburgo', prefix: '+352', flag: '🇱🇺' },
    { code: 'IS', name: 'Islandia', prefix: '+354', flag: '🇮🇸' },
    { code: 'CA', name: 'Canadá', prefix: '+1', flag: '🇨🇦' },
    { code: 'BR', name: 'Brasil', prefix: '+55', flag: '🇧🇷' },
    { code: 'AU', name: 'Australia', prefix: '+61', flag: '🇦🇺' },
    { code: 'NZ', name: 'Nueva Zelanda', prefix: '+64', flag: '🇳🇿' },
    { code: 'ZA', name: 'Sudáfrica', prefix: '+27', flag: '🇿🇦' },
    { code: 'IN', name: 'India', prefix: '+91', flag: '🇮🇳' },
    { code: 'CN', name: 'China', prefix: '+86', flag: '🇨🇳' },
    { code: 'JP', name: 'Japón', prefix: '+81', flag: '🇯🇵' },
    { code: 'KR', name: 'Corea del Sur', prefix: '+82', flag: '🇰🇷' },
    { code: 'SG', name: 'Singapur', prefix: '+65', flag: '🇸🇬' },
    { code: 'MY', name: 'Malasia', prefix: '+60', flag: '🇲🇾' },
    { code: 'TH', name: 'Tailandia', prefix: '+66', flag: '🇹🇭' },
    { code: 'VN', name: 'Vietnam', prefix: '+84', flag: '🇻🇳' },
    { code: 'PH', name: 'Filipinas', prefix: '+63', flag: '🇵🇭' },
    { code: 'ID', name: 'Indonesia', prefix: '+62', flag: '🇮🇩' },
    { code: 'TR', name: 'Turquía', prefix: '+90', flag: '🇹🇷' },
    { code: 'IL', name: 'Israel', prefix: '+972', flag: '🇮🇱' },
    { code: 'AE', name: 'Emiratos Árabes Unidos', prefix: '+971', flag: '🇦🇪' },
    { code: 'SA', name: 'Arabia Saudita', prefix: '+966', flag: '🇸🇦' },
    { code: 'EG', name: 'Egipto', prefix: '+20', flag: '🇪🇬' },
    { code: 'MA', name: 'Marruecos', prefix: '+212', flag: '🇲🇦' },
    { code: 'TN', name: 'Túnez', prefix: '+216', flag: '🇹🇳' },
    { code: 'DZ', name: 'Argelia', prefix: '+213', flag: '🇩🇿' },
    { code: 'LY', name: 'Libia', prefix: '+218', flag: '🇱🇾' },
    { code: 'NG', name: 'Nigeria', prefix: '+234', flag: '🇳🇬' },
    { code: 'KE', name: 'Kenia', prefix: '+254', flag: '🇰🇪' },
    { code: 'GH', name: 'Ghana', prefix: '+233', flag: '🇬🇭' },
    { code: 'ET', name: 'Etiopía', prefix: '+251', flag: '🇪🇹' },
    { code: 'UG', name: 'Uganda', prefix: '+256', flag: '🇺🇬' },
    { code: 'TZ', name: 'Tanzania', prefix: '+255', flag: '🇹🇿' },
    { code: 'RW', name: 'Ruanda', prefix: '+250', flag: '🇷🇼' },
    { code: 'BI', name: 'Burundi', prefix: '+257', flag: '🇧🇮' },
    { code: 'MZ', name: 'Mozambique', prefix: '+258', flag: '🇲🇿' },
    { code: 'ZW', name: 'Zimbabue', prefix: '+263', flag: '🇿🇼' },
    { code: 'BW', name: 'Botsuana', prefix: '+267', flag: '🇧🇼' },
    { code: 'NA', name: 'Namibia', prefix: '+264', flag: '🇳🇦' },
    { code: 'LS', name: 'Lesoto', prefix: '+266', flag: '🇱🇸' },
    { code: 'SZ', name: 'Esuatini', prefix: '+268', flag: '🇸🇿' },
    { code: 'MG', name: 'Madagascar', prefix: '+261', flag: '🇲🇬' },
    { code: 'MU', name: 'Mauricio', prefix: '+230', flag: '🇲🇺' },
    { code: 'SC', name: 'Seychelles', prefix: '+248', flag: '🇸🇨' },
    { code: 'KM', name: 'Comoras', prefix: '+269', flag: '🇰🇲' },
    { code: 'DJ', name: 'Yibuti', prefix: '+253', flag: '🇩🇯' },
    { code: 'SO', name: 'Somalia', prefix: '+252', flag: '🇸🇴' },
    { code: 'ER', name: 'Eritrea', prefix: '+291', flag: '🇪🇷' },
    { code: 'SS', name: 'Sudán del Sur', prefix: '+211', flag: '🇸🇸' },
    { code: 'SD', name: 'Sudán', prefix: '+249', flag: '🇸🇩' },
    { code: 'TD', name: 'Chad', prefix: '+235', flag: '🇹🇩' },
    { code: 'CF', name: 'República Centroafricana', prefix: '+236', flag: '🇨🇫' },
    { code: 'CM', name: 'Camerún', prefix: '+237', flag: '🇨🇲' },
    { code: 'GA', name: 'Gabón', prefix: '+241', flag: '🇬🇦' },
    { code: 'CG', name: 'República del Congo', prefix: '+242', flag: '🇨🇬' },
    { code: 'CD', name: 'República Democrática del Congo', prefix: '+243', flag: '🇨🇩' },
    { code: 'AO', name: 'Angola', prefix: '+244', flag: '🇦🇴' },
    { code: 'GW', name: 'Guinea-Bisáu', prefix: '+245', flag: '🇬🇼' },
    { code: 'CV', name: 'Cabo Verde', prefix: '+238', flag: '🇨🇻' },
    { code: 'ST', name: 'Santo Tomé y Príncipe', prefix: '+239', flag: '🇸🇹' },
    { code: 'SL', name: 'Sierra Leona', prefix: '+232', flag: '🇸🇱' },
    { code: 'LR', name: 'Liberia', prefix: '+231', flag: '🇱🇷' },
    { code: 'CI', name: 'Costa de Marfil', prefix: '+225', flag: '🇨🇮' },
    { code: 'BF', name: 'Burkina Faso', prefix: '+226', flag: '🇧🇫' },
    { code: 'ML', name: 'Malí', prefix: '+223', flag: '🇲🇱' },
    { code: 'NE', name: 'Níger', prefix: '+227', flag: '🇳🇪' },
    { code: 'SN', name: 'Senegal', prefix: '+221', flag: '🇸🇳' },
    { code: 'GM', name: 'Gambia', prefix: '+220', flag: '🇬🇲' },
    { code: 'GN', name: 'Guinea', prefix: '+224', flag: '🇬🇳' },
    { code: 'TG', name: 'Togo', prefix: '+228', flag: '🇹🇬' },
    { code: 'BJ', name: 'Benín', prefix: '+229', flag: '🇧🇯' },
    { code: 'MR', name: 'Mauritania', prefix: '+222', flag: '🇲🇷' },
    { code: 'EH', name: 'Sahara Occidental', prefix: '+212', flag: '🇪🇭' }
];

function initPhoneCountrySelector() {
    const phoneInputs = document.querySelectorAll('.phone-country-selector');
    
    phoneInputs.forEach(container => {
        const select = container.querySelector('.country-select');
        const input = container.querySelector('.phone-input');
        const hiddenInput = container.querySelector('.phone-hidden-input');

        countries.forEach(country => {
            const option = document.createElement('option');
            option.value = country.prefix;
            option.textContent = `${country.flag} ${country.prefix} ${country.name}`;
            option.dataset.countryCode = country.code;
            select.appendChild(option);
        });

        select.value = '+34';

        select.addEventListener('change', function() {
            updatePhoneValue();
        });

        input.addEventListener('input', function() {
            updatePhoneValue();
        });
        
        function updatePhoneValue() {
            const prefix = select.value;
            const number = input.value.replace(/\D/g, '');
            const fullNumber = prefix + number;
            hiddenInput.value = fullNumber;
        }
        
        if (hiddenInput.value) {
            const fullNumber = hiddenInput.value;
            for (const country of countries) {
                if (fullNumber.startsWith(country.prefix)) {
                    select.value = country.prefix;
                    input.value = fullNumber.substring(country.prefix.length);
                    break;
                }
            }
        }
    });
}

document.addEventListener('DOMContentLoaded', initPhoneCountrySelector); 