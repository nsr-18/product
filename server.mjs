
import express from "express";
import cors from "cors";
import bcrypt from "bcrypt";
import jwt from "jsonwebtoken";
import { Sequelize, DataTypes } from "sequelize";

const app = express();
app.use(cors());
app.use(express.json());

const sequelize = new Sequelize("learnapp", "postgres", "yourpassword", {
  host: "localhost",
  dialect: "postgres",
});

const User = sequelize.define("User", {
  username: { type: DataTypes.STRING, unique: true },
  email: { type: DataTypes.STRING, unique: true },
  password_hash: DataTypes.TEXT,
});

const Course = sequelize.define("Course", {
  title: DataTypes.STRING,
  duration: DataTypes.STRING,
  complexity: DataTypes.STRING,
});

const Module = sequelize.define("Module", {
  title: DataTypes.STRING,
  text_content: DataTypes.TEXT,
});

// Relations
User.hasMany(Course, { foreignKey: "user_id" });
Course.belongsTo(User, { foreignKey: "user_id" });
Course.hasMany(Module, { foreignKey: "course_id" });
Module.belongsTo(Course, { foreignKey: "course_id" });

// ---- Auth Routes ----
app.post("/api/register", async (req, res) => {
  const { username, email, password } = req.body;
  const password_hash = await bcrypt.hash(password, 10);
  const user = await User.create({ username, email, password_hash });
  res.json(user);
});

app.post("/api/login", async (req, res) => {
  const { email, password } = req.body;
  const user = await User.findOne({ where: { email } });
  if (!user) return res.status(400).json({ error: "Invalid credentials" });

  const valid = await bcrypt.compare(password, user.password_hash);
  if (!valid) return res.status(400).json({ error: "Invalid credentials" });

  const token = jwt.sign({ id: user.id }, "secretkey", { expiresIn: "1h" });
  res.json({ token });
});

// ---- Course Routes ----
app.get("/api/courses", async (req, res) => {
  const courses = await Course.findAll({ include: User });
  res.json(courses);
});

app.post("/api/courses", async (req, res) => {
  const { userId, title, duration, complexity } = req.body;
  const course = await Course.create({ user_id: userId, title, duration, complexity });
  res.json(course);
});

// ---- Module Routes ----
app.get("/api/courses/:courseId/modules", async (req, res) => {
  const modules = await Module.findAll({ where: { course_id: req.params.courseId } });
  res.json(modules);
});

app.post("/api/courses/:courseId/modules", async (req, res) => {
  const { title, text_content } = req.body;
  const module = await Module.create({ course_id: req.params.courseId, title, text_content });
  res.json(module);
});

// ---- Sync DB & Start ----
sequelize.sync().then(() => {
  app.listen(3000, () => console.log("Backend running on http://localhost:3000"));
});
