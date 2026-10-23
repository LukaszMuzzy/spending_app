export function formatMoney(value, currency = '£') {
    const amount = Number(value ?? 0);
    const formatted = amount.toLocaleString('en-GB', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });

    return `${currency}${formatted}`;
}

export function categoryColor(id) {
    const palette = [
        '#0f766e',
        '#0369a1',
        '#7c3aed',
        '#be185d',
        '#b45309',
        '#15803d',
        '#4338ca',
        '#c2410c',
        '#0e7490',
        '#a16207',
    ];

    const index = Math.abs(Number(id) || 0) % palette.length;

    return palette[index];
}
