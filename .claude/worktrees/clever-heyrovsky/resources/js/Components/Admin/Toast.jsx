import { useEffect, useState } from "react";

export default function Toast({ message, type = "success" }) {
    const [visible, setVisible] = useState(false);

    useEffect(() => {
        if (message) {
            setVisible(true);
            const timer = setTimeout(() => setVisible(false), 3500);
            return () => clearTimeout(timer);
        }
    }, [message]);

    if (!message) return null;

    return (
        <div
            className={`fixed top-6 right-6 z-50 transition-all duration-500
                ${visible ? "opacity-100 translate-y-0" : "opacity-0 -translate-y-4"}`}
        >
            <div className={`px-6 py-4 rounded-xl shadow-lg text-white
                ${type === "success" ? "bg-[#25A7CA]" : "bg-red-500"}`}>
                {message}
            </div>
        </div>
    );
}
