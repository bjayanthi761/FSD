package com.campus.eventmanagement.controller;

import jakarta.servlet.http.HttpSession;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.*;

@Controller
public class LoginController {

    @GetMapping("/login")
    public String loginPage() {
        return "login";
    }

    @PostMapping("/doLogin")
    public String doLogin(
            @RequestParam String username,
            @RequestParam String password,
            @RequestParam String role,
            HttpSession session,
            Model model) {

        if(role.equals("ADMIN")) {

            if(username.equals("admin") && password.equals("admin123")) {
                session.setAttribute("role", "ADMIN");
                return "redirect:/";
            }
            else {
                model.addAttribute("error", "Invalid Admin Credentials");
                return "login";
            }
        }

        else {
}
