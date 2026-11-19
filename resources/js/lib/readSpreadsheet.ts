/*
|--------------------------------------------------------------------------
| Lector mínimo de hojas de cálculo (.xlsx / .csv) sin dependencias
|--------------------------------------------------------------------------
|
| Lee la PRIMERA hoja de un .xlsx (o un .csv) y devuelve sus filas como
| arreglos de texto. Usa DecompressionStream y DOMParser del navegador.
|
*/

type Row = string[];

interface ZipEntry {
    method: number;
    compressedSize: number;
    localHeaderOffset: number;
}

const decoder = new TextDecoder('utf-8');

function readZipEntries(bytes: Uint8Array): Map<string, ZipEntry> {
    const view = new DataView(bytes.buffer, bytes.byteOffset, bytes.byteLength);

    // Buscar "End of central directory" (firma 0x06054b50) desde el final.
    let eocd = -1;

    for (let i = bytes.length - 22; i >= Math.max(0, bytes.length - 65557); i--) {
        if (view.getUint32(i, true) === 0x06054b50) {
            eocd = i;
            break;
        }
    }

    if (eocd < 0) {
        throw new Error('El archivo no es un Excel (.xlsx) válido.');
    }

    const total = view.getUint16(eocd + 10, true);
    let offset = view.getUint32(eocd + 16, true);
    const entries = new Map<string, ZipEntry>();

    for (let i = 0; i < total; i++) {
        if (view.getUint32(offset, true) !== 0x02014b50) {
            break;
        }

        const method = view.getUint16(offset + 10, true);
        const compressedSize = view.getUint32(offset + 20, true);
        const nameLength = view.getUint16(offset + 28, true);
        const extraLength = view.getUint16(offset + 30, true);
        const commentLength = view.getUint16(offset + 32, true);
        const localHeaderOffset = view.getUint32(offset + 42, true);
        const name = decoder.decode(
            bytes.subarray(offset + 46, offset + 46 + nameLength),
        );

        entries.set(name, { method, compressedSize, localHeaderOffset });
        offset += 46 + nameLength + extraLength + commentLength;
    }

    return entries;
}

async function readZipFile(
    bytes: Uint8Array,
    entries: Map<string, ZipEntry>,
    name: string,
): Promise<string | null> {
    const entry = entries.get(name);

    if (!entry) {
        return null;
    }

    const view = new DataView(bytes.buffer, bytes.byteOffset, bytes.byteLength);
    const start = entry.localHeaderOffset;
    const nameLength = view.getUint16(start + 26, true);
    const extraLength = view.getUint16(start + 28, true);
    const dataStart = start + 30 + nameLength + extraLength;
    const data = bytes.subarray(dataStart, dataStart + entry.compressedSize);

    if (entry.method === 0) {
        return decoder.decode(data);
    }

    if (entry.method !== 8) {
        throw new Error('Formato de compresión no soportado.');
    }

    const stream = new Blob([data.slice()])
        .stream()
        .pipeThrough(new DecompressionStream('deflate-raw'));

    return await new Response(stream).text();
}

function columnIndex(ref: string): number {
    const letters = ref.replace(/[0-9]/g, '').toUpperCase();
    let index = 0;

    for (const letter of letters) {
        index = index * 26 + (letter.charCodeAt(0) - 64);
    }

    return index - 1;
}

function parseXml(xml: string): Document {
    return new DOMParser().parseFromString(xml, 'application/xml');
}

async function readXlsx(buffer: ArrayBuffer): Promise<Row[]> {
    const bytes = new Uint8Array(buffer);
    const entries = readZipEntries(bytes);

    // Textos compartidos
    const sharedStrings: string[] = [];
    const sharedXml = await readZipFile(bytes, entries, 'xl/sharedStrings.xml');

    if (sharedXml) {
        for (const si of Array.from(parseXml(sharedXml).getElementsByTagName('si'))) {
            sharedStrings.push(
                Array.from(si.getElementsByTagName('t'))
                    .map((t) => t.textContent ?? '')
                    .join(''),
            );
        }
    }

    // Primera hoja según workbook.xml + relaciones
    let sheetPath = 'xl/worksheets/sheet1.xml';
    const workbookXml = await readZipFile(bytes, entries, 'xl/workbook.xml');
    const relsXml = await readZipFile(bytes, entries, 'xl/_rels/workbook.xml.rels');

    if (workbookXml && relsXml) {
        const firstSheet = parseXml(workbookXml).getElementsByTagName('sheet')[0];
        const relId =
            firstSheet?.getAttribute('r:id') ??
            firstSheet?.getAttributeNS(
                'http://schemas.openxmlformats.org/officeDocument/2006/relationships',
                'id',
            );

        const rel = Array.from(
            parseXml(relsXml).getElementsByTagName('Relationship'),
        ).find((r) => r.getAttribute('Id') === relId);

        const target = rel?.getAttribute('Target');

        if (target) {
            sheetPath = target.startsWith('/')
                ? target.slice(1)
                : `xl/${target}`;
        }
    }

    const sheetXml = await readZipFile(bytes, entries, sheetPath);

    if (!sheetXml) {
        throw new Error('No se encontró ninguna hoja en el archivo.');
    }

    const rows: Row[] = [];

    for (const rowEl of Array.from(parseXml(sheetXml).getElementsByTagName('row'))) {
        const row: Row = [];
        let nextIndex = 0;

        for (const cell of Array.from(rowEl.getElementsByTagName('c'))) {
            const ref = cell.getAttribute('r');
            const index = ref ? columnIndex(ref) : nextIndex;
            const type = cell.getAttribute('t');
            let value = '';

            if (type === 'inlineStr') {
                value = Array.from(cell.getElementsByTagName('t'))
                    .map((t) => t.textContent ?? '')
                    .join('');
            } else {
                const raw = cell.getElementsByTagName('v')[0]?.textContent ?? '';
                value = type === 's' ? (sharedStrings[Number(raw)] ?? '') : raw;
            }

            row[index] = value.trim();
            nextIndex = index + 1;
        }

        rows.push(Array.from(row, (v) => v ?? ''));
    }

    return rows;
}

function readCsv(text: string): Row[] {
    const firstLine = text.split(/\r?\n/, 1)[0] ?? '';
    const separator =
        (firstLine.match(/;/g)?.length ?? 0) > (firstLine.match(/,/g)?.length ?? 0)
            ? ';'
            : ',';

    return text
        .replace(/^﻿/, '')
        .split(/\r?\n/)
        .filter((line) => line.trim() !== '')
        .map((line) =>
            line
                .split(separator)
                .map((v) => v.trim().replace(/^"(.*)"$/, '$1')),
        );
}

export async function readSpreadsheet(file: File): Promise<Row[]> {
    const name = file.name.toLowerCase();

    if (name.endsWith('.csv') || name.endsWith('.txt')) {
        return readCsv(await file.text());
    }

    if (name.endsWith('.xlsx')) {
        return readXlsx(await file.arrayBuffer());
    }

    throw new Error(
        'Formato no soportado. Guarda el archivo como .xlsx o .csv.',
    );
}

/*
|--------------------------------------------------------------------------
| Extraer participantes (folio, nombres y apellidos)
|--------------------------------------------------------------------------
|
| Busca la fila de encabezados que tenga una columna con "Folio"
| (p. ej. "Número / Folio"), otra con "Nombre(s)" y otra con
| "Apellido(s)". Las filas sin folio o sin nombre/apellidos se omiten.
|
*/

export interface DiscountParticipant {
    folio: string;
    first_name: string;
    last_name: string;
}

function headerKey(cell: string): string {
    return String(cell ?? '')
        .normalize('NFD')
        .replace(/[̀-ͯ]/g, '')
        .toLowerCase()
        .trim();
}

function cleanText(value: string): string {
    return String(value ?? '').replace(/\s+/g, ' ').trim();
}

function cleanFolio(value: string): string {
    const folio = cleanText(value);

    // Excel a veces guarda números como "12.0"
    return /^\d+\.0+$/.test(folio) ? folio.replace(/\.0+$/, '') : folio;
}

export function extractParticipants(rows: Row[]): {
    participants: DiscountParticipant[];
    incomplete: number;
    duplicates: number;
} {
    let folioCol = -1;
    let firstNameCol = -1;
    let lastNameCol = -1;

    const headerRow = rows.findIndex((row) => {
        const keys = row.map(headerKey);

        folioCol = keys.findIndex((k) => k.includes('folio'));
        lastNameCol = keys.findIndex((k) => k.includes('apellido'));
        firstNameCol = keys.findIndex(
            (k) => k.includes('nombre') && !k.includes('apellido'),
        );

        return folioCol >= 0 && firstNameCol >= 0 && lastNameCol >= 0;
    });

    if (headerRow < 0) {
        throw new Error(
            'No se encontraron las columnas "Folio", "Nombres" y "Apellidos" en la primera hoja.',
        );
    }

    const found = new Map<string, DiscountParticipant>();
    let incomplete = 0;
    let duplicates = 0;

    for (const row of rows.slice(headerRow + 1)) {
        const folio = cleanFolio(row[folioCol] ?? '');
        const firstName = cleanText(row[firstNameCol] ?? '');
        const lastName = cleanText(row[lastNameCol] ?? '');

        if (!folio && !firstName && !lastName) {
            continue;
        }

        if (!folio || !firstName || !lastName) {
            incomplete++;
            continue;
        }

        if (found.has(folio)) {
            duplicates++;
        }

        found.set(folio, {
            folio,
            first_name: firstName,
            last_name: lastName,
        });
    }

    return {
        participants: Array.from(found.values()),
        incomplete,
        duplicates,
    };
}
