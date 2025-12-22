# 🎯 Product Information Buttons - Complete Guide

## 📍 Location: AI Products Page

When you navigate to **🤖 AI Products** in the web app, you'll see product cards with TWO buttons:

---

## 🟢 GREEN "View" BUTTON

### What It Does
**Displays medical information from OpenAI**

### Data Source
- **OpenAI API** (GPT-3.5-turbo)
- **Real-time** data
- **NOT** from database

### What You'll See
```
🤖 Medical Information (from OpenAI)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Active Ingredient: [from OpenAI]
Therapeutic Class: [from OpenAI]
Mechanism of Action: [from OpenAI]
Indications: [from OpenAI]
Dosage: [from OpenAI]
Administration: [from OpenAI]
Contraindications: [from OpenAI] ⚠️ RED
Side Effects: [from OpenAI]
Drug Interactions: [from OpenAI]
Warnings: [from OpenAI] ⚠️ RED
Pharmacokinetics: [from OpenAI]
Clinical Efficacy: [from OpenAI]
```

### How It Works
1. Click **View** button
2. System calls OpenAI API
3. Fetches medical information
4. Displays in blue section
5. Shows stored data as fallback (green section)

---

## 🔵 BLUE "Edit" BUTTON

### What It Does
**Allows you to edit and store medical information in database**

### Data Source
- **Manual entry** (you type it)
- **Stored in database**
- **Persistent** (saved for later)

### What You Can Edit
- Active ingredients
- Side effects
- Indications
- Dosage information
- Contraindications
- Drug interactions
- Storage requirements

### How It Works
1. Click **Edit** button
2. Fill in medical information
3. Click **Save**
4. Data stored in database
5. Appears in green section on View page

---

## 📊 Comparison Table

| Aspect | 🟢 View Button | 🔵 Edit Button |
|--------|---|---|
| **Source** | OpenAI API | Database |
| **Real-time** | ✅ YES | ❌ NO |
| **Editable** | ❌ NO | ✅ YES |
| **Persistent** | ❌ NO | ✅ YES |
| **Cost** | Uses API credits | Free |
| **Speed** | 2-5 seconds | Instant |
| **Data** | Medical/Scientific | Manual Entry |

---

## 🔄 Data Display Priority

When viewing a product:

1. **First**: OpenAI Medical Information (Blue section)
   - Real-time from OpenAI
   - Always fresh
   - Requires API key

2. **Second**: Stored Information (Green section)
   - From database
   - Manually entered
   - Fallback if OpenAI fails

3. **Error**: If both fail
   - Shows error message
   - Check API key
   - Check internet connection

---

## 💡 Use Cases

### Use 🟢 View Button When:
- You want **latest medical information**
- You need **scientific accuracy**
- You want **real-time data**
- You're **researching** a drug

### Use 🔵 Edit Button When:
- You want to **store information** locally
- You want **quick access** without API calls
- You want to **customize** the data
- You want **offline access**

---

## ⚙️ Requirements

### For 🟢 View Button
- ✅ OpenAI API key in `.env`
- ✅ Internet connection
- ✅ API credits available

### For 🔵 Edit Button
- ✅ Database connection
- ✅ No API key needed
- ✅ Works offline

---

## 🎯 Summary

**🟢 GREEN "View"** = OpenAI Medical Data (Real-time)
**🔵 BLUE "Edit"** = Store Data in Database (Persistent)

**Both buttons work together!** 🎉

