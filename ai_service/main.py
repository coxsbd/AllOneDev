from __future__ import annotations

import os
from typing import Literal

from fastapi import FastAPI, HTTPException
from openai import AsyncOpenAI
from pydantic import BaseModel, Field

app = FastAPI(title="AllOne AI Service", version="1.0.0")
client = AsyncOpenAI(api_key=os.getenv("OPENAI_API_KEY", "not-configured"))

INSTRUCTIONS = {
    "chat": "You are AllOne AI, a helpful, clear, thoughtful assistant. Be accurate and concise. If unsure, say so.",
    "write": "You are AllOne AI, an expert writing assistant. Draft, rewrite, or edit text to fit the user's requested tone and purpose.",
    "summarize": "You are AllOne AI, an expert summarizer. Faithfully summarize provided text, preserve important details, and never invent facts.",
}

class Message(BaseModel):
    role: Literal["user", "assistant"]
    content: str = Field(min_length=1, max_length=12000)

class ChatRequest(BaseModel):
    mode: Literal["chat", "write", "summarize"]
    messages: list[Message] = Field(min_length=1, max_length=20)

@app.get("/health")
async def health():
    return {"status": "ok"}

@app.post("/chat")
async def chat(payload: ChatRequest):
    if not os.getenv("OPENAI_API_KEY"):
        raise HTTPException(status_code=503, detail="AI provider is not configured. Set OPENAI_API_KEY for the Python service.")
    try:
        result = await client.chat.completions.create(
            model=os.getenv("OPENAI_MODEL", "gpt-4o-mini"),
            messages=[{"role": "system", "content": INSTRUCTIONS[payload.mode]}, *[item.model_dump() for item in payload.messages]],
            temperature=0.3 if payload.mode == "summarize" else 0.7,
        )
        answer = result.choices[0].message.content
        if not answer:
            raise HTTPException(status_code=502, detail="The AI provider returned an empty answer.")
        return {"answer": answer}
    except HTTPException:
        raise
    except Exception as exc:
        print(f"AI provider request failed: {exc}")
        raise HTTPException(status_code=502, detail="AI provider request failed. Check the service key, model access, and provider billing.") from exc
