CardService.newCardBuilder()
  .setHeader(CardService.newCardHeader().setTitle("A title"))
  .addSection(
    CardService.newCardSection()
      .setHeader("section heading")
      .addWidget(CardService.newTextParagraph().setText("para test"))
  )
  .build();