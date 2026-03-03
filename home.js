// Registration Simulation
document.addEventListener('DOMContentLoaded', () => {
    

    const faqItems = document.querySelectorAll('.faq-item');

    faqItems.forEach(item => {
      const question = item.querySelector('.faq-question');
    
      question.addEventListener('click', () => {
        // Close all other items
        faqItems.forEach(otherItem => {
          if (otherItem !== item) {
            otherItem.classList.remove('active');
            otherItem.querySelector('.faq-answer').style.maxHeight = null;
            otherItem.querySelector('.faq-icon').textContent = '+';
          }
        });
    
        // Toggle current item
        item.classList.toggle('active');
        const answer = item.querySelector('.faq-answer');
        const icon = item.querySelector('.faq-icon');
    
        if (item.classList.contains('active')) {
          answer.style.maxHeight = answer.scrollHeight + 'px';
          icon.textContent = '–';
        } else {
          answer.style.maxHeight = null;
          icon.textContent = '+';
        }
      });
    });
    
  });

  

  