"use client";

import { useEditor, EditorContent } from '@tiptap/react';
import StarterKit from '@tiptap/starter-kit';
import Image from '@tiptap/extension-image';
import Youtube from '@tiptap/extension-youtube';
import Link from '@tiptap/extension-link';
import Placeholder from '@tiptap/extension-placeholder';
import {
  Bold, Italic,
  Quote, Code, Image as ImageIcon,
  Youtube as YoutubeIcon, Link as LinkIcon,
  Plus
} from 'lucide-react';
import { useCallback, useState, useEffect, useRef } from 'react';

type TiptapEditorProps = {
  content: string;
  onChange: (content: string) => void;
};

export default function TiptapEditor({ content, onChange }: TiptapEditorProps) {
  const [showPlusMenu, setShowPlusMenu] = useState(false);
  const [plusMenuPos, setPlusMenuPos] = useState<{ top: number; left: number } | null>(null);
  const [bubbleMenuPos, setBubbleMenuPos] = useState<{ top: number; left: number } | null>(null);
  const [showBubbleMenu, setShowBubbleMenu] = useState(false);
  const editorWrapperRef = useRef<HTMLDivElement>(null);

  const editor = useEditor({
    immediatelyRender: false,
    extensions: [
      StarterKit.configure({
        heading: {
          levels: [2, 3, 4],
        },
      }),
      Placeholder.configure({
        placeholder: 'Tell your story...',
      }),
      Image.configure({
        inline: true,
        allowBase64: true,
      }),
      Youtube.configure({
        inline: false,
      }),
      Link.configure({
        openOnClick: false,
        autolink: true,
      }),
    ],
    content,
    editorProps: {
      attributes: {
        class: 'focus:outline-none min-h-[500px] py-4'
      },
    },
    onUpdate: ({ editor }) => {
      onChange(editor.getHTML());
    },
    onSelectionUpdate: ({ editor }) => {
      const { from, to } = editor.state.selection;
      const hasSelection = from !== to;

      if (hasSelection && editorWrapperRef.current) {
        const domSelection = window.getSelection();
        if (domSelection && domSelection.rangeCount > 0) {
          const range = domSelection.getRangeAt(0);
          const rect = range.getBoundingClientRect();
          const wrapperRect = editorWrapperRef.current.getBoundingClientRect();
          setBubbleMenuPos({
            top: rect.top - wrapperRect.top - 50,
            left: rect.left - wrapperRect.left + rect.width / 2,
          });
          setShowBubbleMenu(true);
        }
      } else {
        setShowBubbleMenu(false);
      }

      // Plus button for empty lines
      if (!hasSelection) {
        const { $from } = editor.state.selection;
        const currentNode = $from.parent;
        const isEmptyParagraph = currentNode.type.name === 'paragraph' && currentNode.content.size === 0;

        if (isEmptyParagraph && editorWrapperRef.current) {
          try {
            const coords = editor.view.coordsAtPos($from.pos);
            const wrapperRect = editorWrapperRef.current.getBoundingClientRect();
            setPlusMenuPos({
              top: coords.top - wrapperRect.top - 4,
              left: -48,
            });
          } catch {
            setPlusMenuPos(null);
          }
        } else {
          setPlusMenuPos(null);
          setShowPlusMenu(false);
        }
      } else {
        setPlusMenuPos(null);
        setShowPlusMenu(false);
      }
    },
  });

  const addImage = useCallback(() => {
    const url = window.prompt('Enter image URL:');
    if (url && editor) {
      editor.chain().focus().setImage({ src: url }).run();
    }
    setShowPlusMenu(false);
  }, [editor]);

  const addYoutubeVideo = useCallback(() => {
    const url = prompt('Enter YouTube URL:');
    if (url && editor) {
      editor.commands.setYoutubeVideo({
        src: url,
        width: 640,
        height: 480,
      });
    }
    setShowPlusMenu(false);
  }, [editor]);

  const setLink = useCallback(() => {
    if (!editor) return;
    const previousUrl = editor.getAttributes('link').href;
    const url = window.prompt('URL', previousUrl);
    if (url === null) return;
    if (url === '') {
      editor.chain().focus().extendMarkRange('link').unsetLink().run();
      return;
    }
    editor.chain().focus().extendMarkRange('link').setLink({ href: url }).run();
  }, [editor]);

  if (!editor) return null;

  const btnClass = "p-2 rounded-full hover:bg-gray-700 text-gray-300 hover:text-white transition-colors cursor-pointer flex items-center justify-center";
  const activeBtnClass = "p-2 rounded-full bg-white text-black transition-colors flex items-center justify-center";

  return (
    <div className="tiptap-medium-editor relative font-serif" ref={editorWrapperRef}>

      {/* Bubble Menu on text selection */}
      {showBubbleMenu && bubbleMenuPos && (
        <div
          className="absolute z-50 flex bg-gray-900 rounded-full shadow-2xl border border-gray-700 px-2 py-1 gap-1 -translate-x-1/2 animate-[fadeIn_0.1s_ease-out]"
          style={{ top: bubbleMenuPos.top, left: bubbleMenuPos.left }}
          onMouseDown={(e) => e.preventDefault()}
        >
          <button type="button" onClick={() => editor.chain().focus().toggleBold().run()} className={editor.isActive('bold') ? activeBtnClass : btnClass}>
            <Bold size={16} />
          </button>
          <button type="button" onClick={() => editor.chain().focus().toggleItalic().run()} className={editor.isActive('italic') ? activeBtnClass : btnClass}>
            <Italic size={16} />
          </button>
          <button type="button" onClick={setLink} className={editor.isActive('link') ? activeBtnClass : btnClass}>
            <LinkIcon size={16} />
          </button>
          <div className="w-px h-6 bg-gray-600 mx-1 self-center"></div>
          <button type="button" onClick={() => editor.chain().focus().toggleHeading({ level: 2 }).run()} className={editor.isActive('heading', { level: 2 }) ? activeBtnClass : btnClass}>
            <span className="font-bold text-sm">H</span>
          </button>
          <button type="button" onClick={() => editor.chain().focus().toggleBlockquote().run()} className={editor.isActive('blockquote') ? activeBtnClass : btnClass}>
            <Quote size={16} />
          </button>
        </div>
      )}

      {/* Plus Floating Menu on empty lines */}
      {plusMenuPos && (
        <div
          className="absolute z-40 flex items-center gap-2"
          style={{ top: plusMenuPos.top, left: plusMenuPos.left }}
          onMouseDown={(e) => e.preventDefault()}
        >
          <button
            type="button"
            onClick={() => setShowPlusMenu(!showPlusMenu)}
            className={`w-9 h-9 rounded-full border border-gray-600 flex items-center justify-center text-gray-400 hover:text-white hover:border-white transition-all duration-200 ${showPlusMenu ? 'rotate-45 border-white text-white' : ''}`}
          >
            <Plus size={20} />
          </button>

          {showPlusMenu && (
            <div className="flex gap-2 animate-[fadeIn_0.15s_ease-out]">
              <button
                type="button"
                onClick={addImage}
                className="w-9 h-9 rounded-full bg-gray-900 border border-gray-700 flex items-center justify-center text-green-400 hover:bg-gray-800 hover:border-green-400/50 transition-colors"
                title="Add Image"
              >
                <ImageIcon size={17} />
              </button>
              <button
                type="button"
                onClick={addYoutubeVideo}
                className="w-9 h-9 rounded-full bg-gray-900 border border-gray-700 flex items-center justify-center text-red-500 hover:bg-gray-800 hover:border-red-400/50 transition-colors"
                title="Add Video"
              >
                <YoutubeIcon size={17} />
              </button>
              <button
                type="button"
                onClick={() => { editor.chain().focus().toggleCodeBlock().run(); setShowPlusMenu(false); }}
                className="w-9 h-9 rounded-full bg-gray-900 border border-gray-700 flex items-center justify-center text-blue-400 hover:bg-gray-800 hover:border-blue-400/50 transition-colors"
                title="Add Code Block"
              >
                <Code size={17} />
              </button>
            </div>
          )}
        </div>
      )}

      <EditorContent editor={editor} />

      <style dangerouslySetInnerHTML={{ __html: `
        @keyframes fadeIn {
          from { opacity: 0; transform: translateY(4px); }
          to { opacity: 1; transform: translateY(0); }
        }
        .tiptap-medium-editor .ProseMirror {
          line-height: 2;
          font-family: ui-serif, Georgia, Cambria, "Times New Roman", Times, serif;
          font-size: 1.25rem;
          color: #d1d5db;
        }
        .tiptap-medium-editor .ProseMirror p {
           margin-bottom: 1.25em;
        }
        .tiptap-medium-editor .ProseMirror h2 {
          font-size: 1.75rem;
          font-weight: 700;
          color: white;
          margin-top: 2.5rem;
          margin-bottom: 1rem;
          line-height: 1.3;
        }
        .tiptap-medium-editor .ProseMirror h3 {
          font-size: 1.35rem;
          font-weight: 700;
          color: white;
          margin-top: 2rem;
          margin-bottom: 0.75rem;
          line-height: 1.3;
        }
        .tiptap-medium-editor .ProseMirror img {
          max-width: 100%;
          height: auto;
          border-radius: 0.25rem;
          margin: 2.5rem auto;
          display: block;
        }
        .tiptap-medium-editor .ProseMirror iframe {
          max-width: 100%;
          border-radius: 0.25rem;
          margin: 2.5rem auto;
          display: block;
        }
        .tiptap-medium-editor .ProseMirror .is-empty::before {
          content: attr(data-placeholder);
          float: left;
          color: #4b5563;
          pointer-events: none;
          height: 0;
          font-style: italic;
        }
        .tiptap-medium-editor .ProseMirror code {
          background-color: #1f2937;
          padding: 0.2rem 0.4rem;
          border-radius: 0.25rem;
          font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
          font-size: 0.85em;
        }
        .tiptap-medium-editor .ProseMirror pre {
          background-color: #111827;
          padding: 1.5rem;
          border-radius: 0.5rem;
          overflow-x: auto;
          margin: 2rem 0;
        }
        .tiptap-medium-editor .ProseMirror pre code {
          background-color: transparent;
          color: inherit;
          padding: 0;
        }
        .tiptap-medium-editor .ProseMirror blockquote {
          border-left: 3px solid white;
          padding-left: 1.5rem;
          color: #d1d5db;
          font-style: italic;
          margin: 2rem 0;
          font-size: 1.4rem;
          line-height: 1.8;
        }
        .tiptap-medium-editor .ProseMirror a {
          color: #fff;
          text-decoration: underline;
          text-underline-offset: 4px;
        }
        .tiptap-medium-editor .ProseMirror ul {
          list-style-type: disc;
          padding-left: 1.5rem;
          margin-bottom: 1.5rem;
        }
        .tiptap-medium-editor .ProseMirror ol {
          list-style-type: decimal;
          padding-left: 1.5rem;
          margin-bottom: 1.5rem;
        }
        .tiptap-medium-editor .ProseMirror hr {
          border: none;
          border-top: 1px solid #374151;
          margin: 3rem 0;
        }
      `}} />
    </div>
  );
}
