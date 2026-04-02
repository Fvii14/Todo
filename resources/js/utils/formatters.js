/**
 * Capitaliza la primera letra de una cadena
 */
export const capitalizeFirst = (str) => {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
};

/**
 * Genera un slug a partir de un texto
 */
export const generateSlug = (text) => {
    if (!text) return '';
    
    return text
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '') 
        .replace(/[^a-z0-9\s]/g, '') 
        .replace(/\s+/g, '_') 
        .replace(/_+/g, '_') 
        .replace(/^_|_$/g, '');
};

/**
 * Formatea un número como moneda (con separadores de miles)
 */
export const formatCurrency = (value) => {
    if (!value) return '';
    
    const numericString = value.toString().replace(/[^\d]/g, '');
    
    if (numericString === '' || isNaN(numericString)) return '';
    
    const number = parseInt(numericString);
    return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
};

/**
 * Obtiene el valor numérico de un campo formateado como moneda
 */
export const getNumericValue = (value) => {
    if (!value) return null;
    
    const numericString = value.toString().replace(/\./g, '');
    const number = parseInt(numericString);
    
    return isNaN(number) ? null : number;
};

