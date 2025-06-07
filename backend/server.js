const express = require("express");
const cors = require('cors')
const app = express();
const port = 3000;

app.use(cors())

app.get("/", (req, res) => {
  res.json([
    {
      id: 1,
      joke: "Why don't scientists trust atoms? Because they make up everything!",
    },
    { id: 2, joke: "I'm on a seafood diet. I see food and I eat it" },
    { id: 3, joke: "I used to be a baker because I kneaded dough" },
    {
      id: 4,
      joke: "Why don’t some couples go to the gym? Because some relationships don’t work out!",
    },
    {
      id: 5,
      joke: "I'm reading a book on anti-gravity. It's impossible to put down!",
    },
    {
      id: 6,
      joke: "Parallel lines have so much in common. It’s a shame they’ll never meet",
    },
    {
      id: 7,
      joke: "Why did the scarecrow win an award? Because he was outstanding in his field!",
    },
    {
      id: 8,
      joke: "Why don't oysters donate to charity? Because they are shellfish!",
    },
    { id: 9, joke: "I'm on a whiskey diet. I've lost three days already" },
    { id: 10, joke: "I used to play piano by ear, but now I use my hands" },
  ]);
});

app.listen(port, () => console.log(`Listening on port ${port}`));

