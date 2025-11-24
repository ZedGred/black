import { useRef,useState } from "react";

export default function ref() {
  const nameRef = useRef<number>(0);
  const [, forceRender] = useState(0); // dummy state untuk rerender
  const handleCLick = () => {
    nameRef.current = nameRef.current + 1;
    forceRender(i => i + 1); 
  };
  return (
    <div>
      <div>{nameRef.current}</div>
      <div><button className="px-4 py-2 bg-blue-500 rounded-3xl" onClick={handleCLick}>button</button></div>
    </div>
  );
}
