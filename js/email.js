const nodemailer = require('nodemailer');
require('dotenv').config();

const transporter = nodemailer.createTransport({
  host: process.env.SMTP_HOST,
  port: process.env.SMTP_PORT,
  secure: false,
  auth: {
    user: process.env.SMTP_USER,
    pass: process.env.SMTP_PASS
  }
});

const emailService = {
  async sendEmail(to, subject, htmlContent) {
    try {
      const info = await transporter.sendMail({
        from: `${process.env.SENDER_NAME} <${process.env.SENDER_EMAIL}>`,
        to: to,
        subject: subject,
        html: htmlContent
      });
      return { success: true, messageId: info.messageId };
    } catch (error) {
      console.error('Email send error:', error);
      return { success: false, error: error.message };
    }
  },

  assignmentDueEmail(userName, assignmentTitle, dueDate, courseName) {
    return `
      <html>
        <body style="font-family: Arial, sans-serif; color: #333;">
          <h2>Assignment Due Reminder</h2>
          <p>Hi ${userName},</p>
          <p>This is a reminder that your assignment <strong>${assignmentTitle}</strong> for <strong>${courseName}</strong> is due on <strong>${new Date(dueDate).toLocaleDateString()}</strong>.</p>
          <p>Log in to StudSort to view more details and submit your work.</p>
          <br/>
          <p>Best regards,<br/>The StudSort Team</p>
        </body>
      </html>
    `;
  },

  eventUpcomingEmail(userName, eventTitle, eventDate, courseName, eventType) {
    return `
      <html>
        <body style="font-family: Arial, sans-serif; color: #333;">
          <h2>${eventType.charAt(0).toUpperCase() + eventType.slice(1)} Upcoming</h2>
          <p>Hi ${userName},</p>
          <p>You have an upcoming <strong>${eventType}</strong> for <strong>${courseName}</strong>:</p>
          <p><strong>${eventTitle}</strong> on <strong>${new Date(eventDate).toLocaleDateString()}</strong></p>
          <p>Log in to StudSort to view details and prepare.</p>
          <br/>
          <p>Best regards,<br/>The StudSort Team</p>
        </body>
      </html>
    `;
  },

  friendRequestEmail(userName, senderName) {
    return `
      <html>
        <body style="font-family: Arial, sans-serif; color: #333;">
          <h2>New Friend Request</h2>
          <p>Hi ${userName},</p>
          <p><strong>${senderName}</strong> has sent you a friend request on StudSort!</p>
          <p>Log in to accept or decline the request.</p>
          <br/>
          <p>Best regards,<br/>The StudSort Team</p>
        </body>
      </html>
    `;
  },

  groupInviteEmail(userName, groupName, creatorName, courseName) {
    return `
      <html>
        <body style="font-family: Arial, sans-serif; color: #333;">
          <h2>Group Invitation</h2>
          <p>Hi ${userName},</p>
          <p><strong>${creatorName}</strong> has invited you to join the group <strong>${groupName}</strong> for <strong>${courseName}</strong>.</p>
          <p>Log in to accept or decline the invitation.</p>
          <br/>
          <p>Best regards,<br/>The StudSort Team</p>
        </body>
      </html>
    `;
  }
};

module.exports = emailService;