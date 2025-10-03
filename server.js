import OpenAI from "openai";
import express from "express";
import dotenv from "dotenv";

dotenv.config();

console.log("👉 server.js loaded");

const app = express();
app.use(express.json());
    const openai = new OpenAI({
      apiKey: process.env.OPENAI_API_KEY
    });
app.post("/generate", async (req, res) => {
  const { rawContent } = req.body;

  if (!process.env.OPENAI_API_KEY) {
    return res.status(500).json({ error: "Missing OPENAI_API_KEY" });
  }
  if (!rawContent) {
    return res.status(400).json({ error: "Missing rawContent" });
  }

  try {
    const response = await fetch("https://api.openai.com/v1/responses", {
      method: "POST",
      headers: {
        "Authorization": `Bearer ${process.env.OPENAI_API_KEY}`,
        "Content-Type": "application/json"
      },
      body: JSON.stringify({
  model: "gpt-4o-mini",
  input: [
    { role: "system", content: "Split this into course modules. Return ONLY valid JSON array of objects like {title, summary}." },
    { role: "user", content: rawContent }
  ],
  text: {
    format: {
      type: "json_schema",
      name: "Modules",
      schema: {
        type: "array",
        items: {
          type: "object",
          properties: {
            title: { type: "string" },
            summary: { type: "string" }
          },
          required: ["title", "summary"]
        }
      },
      strict: true
    }
  }
})

    });

    const data = await response.json();

let modules = [];
try {
  modules = JSON.parse(data.output[0].content[0].text);
} catch (e) {
  console.error("Parse error:", e, data);
}

res.json({ modules });

  } catch (err) {
    console.error("Error talking to OpenAI:", err);
    res.status(500).json({ error: err.message });
  }
});

app.listen(3000, () => {
  console.log("✅ Server running at http://localhost:3000");
});
