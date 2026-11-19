/**
 * Formatea el precio final con descuento (costo total de la inscripción,
 * no la rebaja): 330 → "$330", 99.5 → "$99.50".
 * Devuelve null si no hay monto configurado.
 */
export function formatDiscountAmount(
    amount: number | string | null | undefined,
): string | null {
    const value = Number(amount);

    if (amount === null || amount === undefined || amount === '' || !(value > 0)) {
        return null;
    }

    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN',
        minimumFractionDigits: Number.isInteger(value) ? 0 : 2,
        maximumFractionDigits: 2,
    }).format(value);
}
