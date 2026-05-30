
// 平滑滚动和导航高亮
document.addEventListener('DOMContentLoaded', function() {
    const navItems = document.querySelectorAll('.nav-item');
    const sections = document.querySelectorAll('.section');

    // 导航点击事件
    navItems.forEach(item =&gt; {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href').substring(1);
            const targetSection = document.getElementById(targetId);
            
            if (targetSection) {
                targetSection.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // 滚动高亮导航
    window.addEventListener('scroll', function() {
        let current = '';
        
        sections.forEach(section =&gt; {
            const sectionTop = section.offsetTop;
            if (window.scrollY &gt;= sectionTop - 200) {
                current = section.getAttribute('id');
            }
        });

        navItems.forEach(item =&gt; {
            item.classList.remove('active');
            if (item.getAttribute('href') === '#' + current) {
                item.classList.add('active');
            }
        });
    });
});
