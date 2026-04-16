package com.campus.eventmanagement.controller;

import org.springframework.stereotype.Controller;
import org.springframework.web.bind.annotation.*;

@Controller
public class LoginController {

    @GetMapping("/login")
    public String login() {
        return "login";
    }

    @PostMapping("/login")
    public String process(@RequestParam String username,
                          @RequestParam String password) {

        if(username.equals("admin") && password.equals("admin")) {
            return "redirect:/";
        }
        return "login";
    }
}
