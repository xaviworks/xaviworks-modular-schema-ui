import type { FormEvent } from 'react';

type Field = {
    name: string;
    label: string;
    type: string;
    required: boolean;
    placeholder?: string | null;
    help?: string | null;
    options?: Record<string, string>;
};

export function ModularForm({ form, onSubmit }: { form: { fields: Field[]; values: Record<string, unknown> }; onSubmit: (event: FormEvent<HTMLFormElement>) => void }) {
    return <form onSubmit={onSubmit}>
        {form.fields.map((field) => <label key={field.name}>
            {field.label}
            {field.type === 'select' ? <select name={field.name} defaultValue={String(form.values[field.name] ?? '')}>{Object.entries(field.options ?? {}).map(([value, label]) => <option key={value} value={value}>{label}</option>)}</select> : <input name={field.name} type={field.type} defaultValue={field.type === 'password' ? undefined : String(form.values[field.name] ?? '')} placeholder={field.placeholder ?? undefined} required={field.required} />}
            {field.help && <small>{field.help}</small>}
        </label>)}
        <button type="submit">Submit</button>
    </form>;
}
