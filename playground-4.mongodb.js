/* global use, db */
// MongoDB Playground
// To disable this template go to Settings | MongoDB | Use Default Template For Playground.
// Make sure you are connected to enable completions and to be able to run a playground.
// Use Ctrl+Space inside a snippet or a string literal to trigger completions.
// The result of the last command run in a playground is shown on the results panel.
// By default the first 20 documents will be returned with a cursor.
// Use 'console.log()' to print to the debug output.
// For more documentation on playgrounds please refer to
// https://www.mongodb.com/docs/mongodb-vscode/playgrounds/
use('<Cluster0>');

if (typeof email !== 'undefined' && typeof password !== 'undefined') {
  const user = db.getCollection('users').findOne({ email: email });

  if (user) {
    if (password === user.password) { // Replace with proper password hashing check
      print(`User authenticated. ID: ${user._id}, Firstname: ${user.firstname}`);
      // Simulate session creation or redirection logic here
    } else {
      print("Invalid password.");
    }
  } else {
    print("No user found with that email.");
  }
} else {
  print("Email and password must be provided.");
}
