import { useRef, useEffect, useCallback } from "react";
import { Icon } from "@iconify/react";

export default function RichTextEditor({ value = "", onChange, placeholder = "Escribe aquí..." }) {
    const editorRef = useRef(null);

    useEffect(() => {
        if (editorRef.current && editorRef.current.innerHTML !== value) {
            editorRef.current.innerHTML = value;
        }
    }, []);

    const exec = useCallback((command, val = null) => {
        document.execCommand(command, false, val);
        editorRef.current?.focus();
        handleInput();
    }, []);

    const handleInput = useCallback(() => {
        if (onChange && editorRef.current) {
            onChange(editorRef.current.innerHTML);
        }
    }, [onChange]);

    const cleanHtml = (html) => {
        const tmp = document.createElement('div');
        tmp.innerHTML = html;
        tmp.querySelectorAll('*').forEach((el) => {
            // Remove all data-* attributes
            [...el.attributes].forEach((attr) => {
                if (attr.name.startsWith('data-')) el.removeAttribute(attr.name);
            });
            // Remove empty style attributes
            if (el.getAttribute('style') === '' || el.getAttribute('style')?.trim() === '') {
                el.removeAttribute('style');
            }
        });
        return tmp.innerHTML;
    };

    const handlePaste = useCallback((e) => {
        e.preventDefault();
        const html = e.clipboardData.getData('text/html');
        const text = e.clipboardData.getData('text/plain');
        const clean = html ? cleanHtml(html) : text.replace(/\n/g, '<br>');
        document.execCommand('insertHTML', false, clean);
    }, []);

    const insertLink = () => {
        const url = window.prompt("URL del enlace:");
        if (url) exec("createLink", url);
    };

    const buttons = [
        { icon: "solar:text-bold-outline",       cmd: "bold",          title: "Negrita" },
        { icon: "solar:text-italic-outline",     cmd: "italic",        title: "Cursiva" },
        { icon: "solar:text-underline-outline",  cmd: "underline",     title: "Subrayado" },
        { icon: "solar:list-outline",            cmd: "insertUnorderedList", title: "Lista" },
        { icon: "solar:list-check-outline",      cmd: "insertOrderedList",   title: "Lista numerada" },
        { icon: "solar:align-left-outline",      cmd: "justifyLeft",   title: "Alinear izquierda" },
        { icon: "solar:align-center-outline",    cmd: "justifyCenter", title: "Centrar" },
        { icon: "solar:align-right-outline",     cmd: "justifyRight",  title: "Alinear derecha" },
    ];

    return (
        <div className="border border-[#E5E7EB] rounded-lg overflow-hidden focus-within:ring-2 focus-within:ring-[#25A7CA]/30">
            {/* Toolbar */}
            <div className="flex flex-wrap items-center gap-1 px-3 py-2 bg-gray-50 border-b border-[#E5E7EB]">
                {/* Headings */}
                <select
                    onMouseDown={(e) => e.preventDefault()}
                    onChange={(e) => { exec("formatBlock", e.target.value); e.target.value = ""; }}
                    defaultValue=""
                    className="text-xs border border-gray-200 rounded px-1 py-1 text-gray-600 bg-white"
                >
                    <option value="" disabled>Formato</option>
                    <option value="p">Párrafo</option>
                    <option value="h2">Título H2</option>
                    <option value="h3">Título H3</option>
                    <option value="h4">Título H4</option>
                    <option value="blockquote">Cita</option>
                </select>

                <div className="w-px h-5 bg-gray-300 mx-1" />

                {buttons.map(({ icon, cmd, title }) => (
                    <button
                        key={cmd}
                        type="button"
                        title={title}
                        onMouseDown={(e) => { e.preventDefault(); exec(cmd); }}
                        className="p-1.5 rounded hover:bg-gray-200 text-gray-600 transition"
                    >
                        <Icon icon={icon} width={15} />
                    </button>
                ))}

                <div className="w-px h-5 bg-gray-300 mx-1" />

                <button
                    type="button"
                    title="Insertar enlace"
                    onMouseDown={(e) => { e.preventDefault(); insertLink(); }}
                    className="p-1.5 rounded hover:bg-gray-200 text-gray-600 transition"
                >
                    <Icon icon="solar:link-outline" width={15} />
                </button>
                <button
                    type="button"
                    title="Quitar formato"
                    onMouseDown={(e) => { e.preventDefault(); exec("removeFormat"); }}
                    className="p-1.5 rounded hover:bg-gray-200 text-gray-600 transition"
                >
                    <Icon icon="solar:eraser-outline" width={15} />
                </button>
            </div>

            {/* Content Area */}
            <div
                ref={editorRef}
                contentEditable
                suppressContentEditableWarning
                onInput={handleInput}
                onPaste={handlePaste}
                data-placeholder={placeholder}
                className="min-h-[200px] p-4 text-sm text-gray-800 focus:outline-none prose prose-sm max-w-none
                    [&[data-placeholder]:empty]:before:content-[attr(data-placeholder)]
                    [&[data-placeholder]:empty]:before:text-gray-400"
            />
        </div>
    );
}
