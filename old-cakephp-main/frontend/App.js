
import { useState, useEffect } from "react";

function App() {
  const [error, setError] = useState(false);
  const [jokes, setJokes] = useState([]);

  useEffect(() => {
    const fetchData = async () => {
      try {
        const response = await fetch("http://localhost:8000");
        if (response.ok) {
          const result = await response.json();
          setJokes(result);
          setError(true);
        } else {
          console.error("Error fetching data:", response.statusText);
        }
      } catch (error) {
        console.error("Error fetching data:", error);
      }
    };
    fetchData();
  }, []);

  return (
    <>
      <nav className='bg-red-50 py-4'>
        <p className='font-light text-4xl text-red-400  px-20 '>jokesVille</p>
      </nav>
      <main className='flex justify-center items-center px-6 py-10 bg-red-100'>
        {!error ? (
          <div
            className={`${
              error ? "flex" : "hidden"
            } h-full justify-center items-center text-center`}
          >
            <p className='text-xl font-medium'>
              So Sorry we could not find you jokes
              <span role='img' aria-label='dissapointed' className='text-4xl'>
                &#128542;
              </span>
            </p>
          </div>
        ) : (
          <div className='grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 justify-center items-center gap-x-4 gap-y-3 text-center my-10 py-5'>
            {jokes.map((joke) => (
              <div
                key={joke.id}
                className=' h-40 flex flex-col justify-center items-center bg-gray-50 rounded-md py-16 px-20'
              >
                <span role='img' aria-label='laugh'>
                  &#128514;
                </span>
                <p className='text-gray-500'>{joke.joke}</p>
              </div>
            ))}
          </div>
        )}
      </main>
    </>
  );
}

export default App;