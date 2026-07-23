type Column = { name: string; label: string };

export function ModularTable({ table }: { table: { columns: Column[]; records: Array<Record<string, unknown>>; emptyMessage: string } }) {
    return <table>
        <thead><tr>{table.columns.map((column) => <th key={column.name}>{column.label}</th>)}</tr></thead>
        <tbody>
            {table.records.length === 0 ? <tr><td colSpan={table.columns.length}>{table.emptyMessage}</td></tr> : table.records.map((record, index) => <tr key={index}>{table.columns.map((column) => <td key={column.name}>{String(record[column.name] ?? '')}</td>)}</tr>)}
        </tbody>
    </table>;
}
