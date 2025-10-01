-- USERS
CREATE TABLE users (
  id SERIAL PRIMARY KEY,
  username VARCHAR(50) UNIQUE NOT NULL,
  email VARCHAR(100) UNIQUE NOT NULL,
  password_hash TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT NOW()
);

-- COURSES
CREATE TABLE courses (
  id SERIAL PRIMARY KEY,
  user_id INT REFERENCES users(id) ON DELETE CASCADE,
  title VARCHAR(255) NOT NULL,
  duration VARCHAR(20),
  complexity VARCHAR(20),
  created_at TIMESTAMP DEFAULT NOW()
);

-- MODULES
CREATE TABLE modules (
  id SERIAL PRIMARY KEY,
  course_id INT REFERENCES courses(id) ON DELETE CASCADE,
  title VARCHAR(255) NOT NULL,
  text_content TEXT,
  created_at TIMESTAMP DEFAULT NOW()
);

-- VOCAB
CREATE TABLE vocab (
  id SERIAL PRIMARY KEY,
  module_id INT REFERENCES modules(id) ON DELETE CASCADE,
  term VARCHAR(100),
  definition TEXT
);

-- QUIZZES
CREATE TABLE quizzes (
  id SERIAL PRIMARY KEY,
  module_id INT REFERENCES modules(id) ON DELETE CASCADE,
  question TEXT NOT NULL
);

-- QUIZ ANSWERS
CREATE TABLE quiz_answers (
  id SERIAL PRIMARY KEY,
  quiz_id INT REFERENCES quizzes(id) ON DELETE CASCADE,
  answer_text TEXT NOT NULL,
  is_correct BOOLEAN DEFAULT FALSE
);

-- PROGRESS
CREATE TABLE progress (
  id SERIAL PRIMARY KEY,
  user_id INT REFERENCES users(id) ON DELETE CASCADE,
  module_id INT REFERENCES modules(id) ON DELETE CASCADE,
  status VARCHAR(20) DEFAULT 'incomplete',
  score INT DEFAULT 0
);



